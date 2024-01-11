<?php

declare(strict_types=1);

/*
 * This file is part of rekalogika/mapper package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\Mapper\ObjectCache;

use Rekalogika\Mapper\Exception\CachedTargetObjectNotFoundException;
use Rekalogika\Mapper\Exception\CircularReferenceException;
use Rekalogika\Mapper\Exception\LogicException;
use Rekalogika\Mapper\TypeResolver\TypeResolverInterface;
use Symfony\Component\PropertyInfo\Type;

final class ObjectCache
{
    /**
     * @var \SplObjectStorage<object,\ArrayObject<string,object>>
     */
    private \SplObjectStorage $cache;

    /**
     * @var \SplObjectStorage<object,\ArrayObject<string,true>>
     */
    private \SplObjectStorage $preCache;

    public function __construct(
        private TypeResolverInterface $typeResolver
    ) {
        $this->cache = new \SplObjectStorage();
        $this->preCache = new \SplObjectStorage();
    }

    private function isBlacklisted(mixed $source): bool
    {
        return $source instanceof \DateTimeInterface;
    }

    /**
     * Precaching indicates we want to cache the target, but haven't done so
     * yet. If the object is still in precached status, obtaining it from the
     * cache will yield an exception. If the target is finally cached, it is
     * no longer in precached status.
     *
     * @param mixed $source
     * @param Type $targetType
     * @return void
     */
    public function preCache(mixed $source, Type $targetType): void
    {
        if (!is_object($source)) {
            return;
        }

        if ($this->isBlacklisted($source)) {
            return;
        }

        if (!$this->typeResolver->isSimpleType($targetType)) {
            throw new LogicException('Target type must be simple type');
        }

        $targetTypeString = $this->typeResolver->getTypeString($targetType);

        if (!isset($this->preCache[$source])) {
            /** @var \ArrayObject<string,true> */
            $arrayObject = new \ArrayObject();
            $this->preCache[$source] = $arrayObject;
        }

        $this->preCache->offsetGet($source)->offsetSet($targetTypeString, true);
    }

    private function isPreCached(mixed $source, Type $targetType): bool
    {
        if (!is_object($source)) {
            return false;
        }

        if (!$this->typeResolver->isSimpleType($targetType)) {
            throw new LogicException('Target type must be simple type');
        }

        $targetTypeString = $this->typeResolver->getTypeString($targetType);

        return isset($this->preCache[$source][$targetTypeString]);
    }

    private function removePrecache(mixed $source, Type $targetType): void
    {
        if (!is_object($source)) {
            return;
        }

        if (!$this->typeResolver->isSimpleType($targetType)) {
            throw new LogicException('Target type must be simple type');
        }

        $targetTypeString = $this->typeResolver->getTypeString($targetType);

        if (isset($this->preCache[$source][$targetTypeString])) {
            unset($this->preCache[$source][$targetTypeString]);
        }
    }

    public function containsTarget(mixed $source, Type $targetType): bool
    {
        if (!is_object($source)) {
            return false;
        }

        if ($this->isBlacklisted($source)) {
            return false;
        }

        if (!$this->typeResolver->isSimpleType($targetType)) {
            throw new LogicException('Target type must be simple type');
        }

        $targetTypeString = $this->typeResolver->getTypeString($targetType);

        return isset($this->cache[$source][$targetTypeString]);
    }

    public function getTarget(mixed $source, Type $targetType): mixed
    {
        if ($this->isPreCached($source, $targetType)) {
            throw new CircularReferenceException($source, $targetType);
        }

        if ($this->isBlacklisted($source)) {
            throw new CachedTargetObjectNotFoundException();
        }

        if (!is_object($source)) {
            throw new CachedTargetObjectNotFoundException();
        }

        if (!$this->typeResolver->isSimpleType($targetType)) {
            throw new LogicException('Target type must be simple type');
        }

        $targetTypeString = $this->typeResolver->getTypeString($targetType);

        /** @var object */
        return $this->cache[$source][$targetTypeString]
            ?? throw new CachedTargetObjectNotFoundException();
    }

    public function saveTarget(
        mixed $source,
        Type $targetType,
        mixed $target
    ): void {
        if (!is_object($source) || !is_object($target)) {
            return;
        }

        if ($this->isBlacklisted($source)) {
            return;
        }

        $targetTypeString = $this->typeResolver->getTypeString($targetType);

        if (isset($this->cache[$source][$targetTypeString])) {
            throw new LogicException(sprintf(
                'Target object for source object "%s" and target type "%s" already exists',
                get_class($source),
                $targetTypeString
            ));
        }

        if (!isset($this->cache[$source])) {
            /** @var \ArrayObject<string,object> */
            $arrayObject = new \ArrayObject();
            $this->cache[$source] = $arrayObject;
        }

        $this->cache->offsetGet($source)->offsetSet($targetTypeString, $target);
        $this->removePrecache($source, $targetType);
    }
}