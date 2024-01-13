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

namespace Rekalogika\Mapper\MethodMapper;

use Rekalogika\Mapper\MainTransformer\Context;

interface MapFromObjectInterface
{
    public static function mapFromObject(
        object $source,
        SubMapperInterface $mapper,
        Context $context,
    ): static;
}
