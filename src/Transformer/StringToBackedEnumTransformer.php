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

namespace Rekalogika\Mapper\Transformer;

use Rekalogika\Mapper\Exception\InvalidArgumentException;
use Rekalogika\Mapper\Transformer\Contracts\TransformerInterface;
use Rekalogika\Mapper\Transformer\Contracts\TypeMapping;
use Rekalogika\Mapper\Util\TypeFactory;
use Symfony\Component\PropertyInfo\Type;

final class StringToBackedEnumTransformer implements TransformerInterface
{
    public function transform(
        mixed $source,
        mixed $target,
        ?Type $sourceType,
        ?Type $targetType,
        array $context
    ): mixed {
        if (!is_string($source)) {
            throw new InvalidArgumentException(sprintf('Source must be string, "%s" given', get_debug_type($source)));
        }

        $class = $targetType?->getClassName();

        if ($class === null || !\enum_exists($class)) {
            throw new InvalidArgumentException(sprintf('Target must be an enum class-string, "%s" given', get_debug_type($class)));
        }

        // @todo maybe add option to handle values not in the enum
        if (is_a($class, \BackedEnum::class, true)) {
            /** @var class-string<\BackedEnum> $class */
            return $class::from($source);
        }

        throw new InvalidArgumentException(sprintf('Target must be an enum class-string, "%s" given', get_debug_type($target)));
    }

    public function getSupportedTransformation(): iterable
    {
        yield new TypeMapping(
            TypeFactory::string(),
            TypeFactory::objectOfClass(\BackedEnum::class),
        );
    }
}
