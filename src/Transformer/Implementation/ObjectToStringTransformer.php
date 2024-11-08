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

namespace Rekalogika\Mapper\Transformer\Implementation;

use Rekalogika\Mapper\Context\Context;
use Rekalogika\Mapper\Exception\InvalidArgumentException;
use Rekalogika\Mapper\Transformer\TransformerInterface;
use Rekalogika\Mapper\Transformer\TypeMapping;
use Rekalogika\Mapper\Util\TypeCheck;
use Rekalogika\Mapper\Util\TypeFactory;
use Symfony\Component\PropertyInfo\Type;

/**
 * @todo rename class to ObjectToScalarTransformer
 */
final readonly class ObjectToStringTransformer implements TransformerInterface
{
    #[\Override]
    public function transform(
        mixed $source,
        mixed $target,
        ?Type $sourceType,
        ?Type $targetType,
        Context $context,
    ): mixed {
        if ($source instanceof \Stringable) {
            if (TypeCheck::isString($targetType)) {
                return (string) $source;
            } elseif (TypeCheck::isInt($targetType)) {
                return (int) (string) $source;
            } elseif (TypeCheck::isFloat($targetType)) {
                return (float) (string) $source;
            } elseif (TypeCheck::isBool($targetType)) {
                return (bool) (string) $source;
            } else {
                return (string) $source;
            }
        } elseif ($source instanceof \BackedEnum) {
            return $source->value;
        } elseif ($source instanceof \UnitEnum) {
            return $source->name;
        }

        throw new InvalidArgumentException(\sprintf('Source must be instance of "\Stringable" or "\UnitEnum", "%s" given', get_debug_type($source)), context: $context);
    }

    #[\Override]
    public function getSupportedTransformation(): iterable
    {
        yield new TypeMapping(
            TypeFactory::objectOfClass(\Stringable::class),
            TypeFactory::string(),
        );

        yield new TypeMapping(
            TypeFactory::objectOfClass(\Stringable::class),
            TypeFactory::int(),
        );

        yield new TypeMapping(
            TypeFactory::objectOfClass(\Stringable::class),
            TypeFactory::float(),
        );

        yield new TypeMapping(
            TypeFactory::objectOfClass(\Stringable::class),
            TypeFactory::bool(),
        );

        yield new TypeMapping(
            TypeFactory::objectOfClass(\UnitEnum::class),
            TypeFactory::string(),
        );
    }
}
