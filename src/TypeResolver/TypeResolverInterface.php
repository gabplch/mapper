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

namespace Rekalogika\Mapper\TypeResolver;

use Rekalogika\Mapper\Model\MixedType;
use Symfony\Component\PropertyInfo\Type;

interface TypeResolverInterface
{
    /**
     * Guesses the type of the given variable.
     */
    public function guessTypeFromVariable(mixed $variable): Type;

    public function getTypeString(Type|MixedType $type): string;

    /**
     * Gets all the possible simple types from a Type
     *
     * @param Type|array<array-key,Type> $type
     * @return array<array-key,Type>
     */
    public function getSimpleTypes(Type|array $type): array;

    /**
     * Simple Type is a type that is not nullable, and does not have more
     * than one key type or value type.
     */
    public function isSimpleType(Type $type): bool;

    /**
     * Example: If the variable type is
     * 'IteratorAggregate<int,IteratorAggregate<int,string>>', then this method
     * will return ['IteratorAggregate<int,IteratorAggregate<int,string>>',
     * 'IteratorAggregate<int,Traversable<int,string>>',
     * 'Traversable<int,IteratorAggregate<int,string>>',
     * 'Traversable<int,Traversable<int,string>>']
     *
     * Note: IteratorAggregate extends Traversable
     *
     * @param array<int,Type>|Type|MixedType $type
     * @return array<int,string>
     */
    public function getApplicableTypeStrings(array|Type|MixedType $type): array;
}