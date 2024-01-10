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

namespace Rekalogika\Mapper\Exception;

use Rekalogika\Mapper\Util\TypeCheck;
use Symfony\Component\PropertyInfo\Type;

class CircularReferenceException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(mixed $source, Type $targetType)
    {
        parent::__construct(
            sprintf(
                'Circular reference detected when trying to get the object of type "%s" transformed to "%s"',
                \get_debug_type($source),
                TypeCheck::getDebugType($targetType)
            )
        );
    }
}
