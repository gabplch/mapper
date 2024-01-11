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

namespace Rekalogika\Mapper;

use Psr\Container\ContainerInterface;
use Rekalogika\Mapper\Contracts\MainTransformerAwareInterface;
use Rekalogika\Mapper\Contracts\MainTransformerInterface;
use Rekalogika\Mapper\Contracts\TransformerInterface;
use Rekalogika\Mapper\Exception\LogicException;
use Rekalogika\Mapper\Exception\UnableToFindSuitableTransformerException;
use Rekalogika\Mapper\Mapping\MappingEntry;
use Rekalogika\Mapper\Mapping\MappingFactoryInterface;
use Rekalogika\Mapper\Model\MixedType;
use Rekalogika\Mapper\ObjectCache\ObjectCache;
use Rekalogika\Mapper\ObjectCache\ObjectCacheFactoryInterface;
use Rekalogika\Mapper\TypeResolver\TypeResolverInterface;
use Symfony\Component\PropertyInfo\Type;

class MainTransformer implements MainTransformerInterface
{
    public const OBJECT_CACHE = 'object_cache';

    public function __construct(
        private ContainerInterface $transformersLocator,
        private TypeResolverInterface $typeResolver,
        private MappingFactoryInterface $mappingFactory,
        private ObjectCacheFactoryInterface $objectCacheFactory,
    ) {
    }

    private function getTransformer(string $id): TransformerInterface
    {
        $transformer = $this->transformersLocator->get($id);

        if (!$transformer instanceof TransformerInterface) {
            throw new LogicException(sprintf(
                'Transformer with id "%s" must implement %s',
                $id,
                TransformerInterface::class
            ));
        }

        if ($transformer instanceof MainTransformerAwareInterface) {
            return $transformer->withMainTransformer($this);
        }
        return $transformer;

    }

    public function transform(
        mixed $source,
        mixed $target,
        null|Type|array $targetType,
        array $context
    ): mixed {
        // if targettype is not provided, guess it from target
        // if the target is also missing then throw exception

        if ($targetType === null) {
            if ($target === null) {
                throw new LogicException('Either $target or $targetType must be provided');
            }
            $targetType = $this->typeResolver->guessTypeFromVariable($target);
        }

        // get object cache

        if (!isset($context[self::OBJECT_CACHE])) {
            $objectCache = $this->objectCacheFactory->createObjectCache();
            $context[self::OBJECT_CACHE] = $objectCache;
        } else {
            /** @var ObjectCache */
            $objectCache = $context[self::OBJECT_CACHE];
        }

        // init vars

        $targetType = $this->typeResolver->getSimpleTypes($targetType);
        $sourceType = $this->typeResolver->guessTypeFromVariable($source);

        foreach ($targetType as $singleTargetType) {
            $transformers = $this->getTransformers($sourceType, $singleTargetType);

            foreach ($transformers as $transformer) {
                /** @var mixed */
                $result = $transformer->transform(
                    source: $source,
                    target: $target,
                    sourceType: $sourceType,
                    targetType: $singleTargetType,
                    context: $context
                );

                return $result;
            }
        }

        throw new UnableToFindSuitableTransformerException($sourceType, $targetType);
    }

    /**
     * @param Type|MixedType $sourceType
     * @param Type|MixedType $targetType
     * @return iterable<int,TransformerInterface>
     */
    private function getTransformers(
        Type|MixedType $sourceType,
        Type|MixedType $targetType,
    ): iterable {
        foreach ($this->getTransformerMapping($sourceType, $targetType) as $item) {
            $id = $item->getId();
            yield $this->getTransformer($id);
        }
    }

    /**
     * @param Type|MixedType $sourceType
     * @param Type|MixedType $targetType
     * @return array<int,MappingEntry>
     */
    public function getTransformerMapping(
        Type|MixedType $sourceType,
        Type|MixedType $targetType,
    ): array {
        $sourceTypeStrings = $this->typeResolver
            ->getApplicableTypeStrings($sourceType);

        $targetTypeStrings = $this->typeResolver
            ->getApplicableTypeStrings($targetType);

        return $this->mappingFactory->getMapping()
            ->getMappingBySourceAndTarget($sourceTypeStrings, $targetTypeStrings);
    }
}
