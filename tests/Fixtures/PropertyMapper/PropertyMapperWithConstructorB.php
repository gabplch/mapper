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

namespace Rekalogika\Mapper\Tests\Fixtures\PropertyMapper;

use Rekalogika\Mapper\PropertyMapper\AsPropertyMapper;

#[AsPropertyMapper(
    sourceClass: SomeObject::class,
    targetClass: SomeObjectWithConstructorDto::class,
)]
class PropertyMapperWithConstructorB
{
    #[AsPropertyMapper('propertyB')]
    public function mapPropertyB(SomeObject $object): string
    {
        return $object::class . '::propertyB';
    }
}
