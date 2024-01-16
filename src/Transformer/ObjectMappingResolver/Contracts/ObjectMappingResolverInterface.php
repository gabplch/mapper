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

namespace Rekalogika\Mapper\Transformer\ObjectMappingResolver\Contracts;

use Rekalogika\Mapper\Context\Context;

interface ObjectMappingResolverInterface
{
    /**
     * @param class-string $sourceClass
     * @param class-string $targetClass
     */
    public function resolveObjectMapping(
        string $sourceClass,
        string $targetClass,
        Context $context
    ): ObjectMapping;
}