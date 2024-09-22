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

namespace Rekalogika\Mapper\Tests\Fixtures\MapAttribute;

use Rekalogika\Mapper\Attribute\Map;

class ObjectOverridingSomeObjectDto extends SomeObjectDto
{
    #[Map(property: 'sourcePropertyB')]
    public ?string $targetPropertyA = null;
    private ?string $targetPropertyB = null;
    private ?string $targetPropertyC = null;

    #[Map(property: 'sourcePropertyC')]
    public function setTargetPropertyB(string $value): void
    {
        $this->targetPropertyB = $value;
    }

    public function getTargetPropertyB(): ?string
    {
        return $this->targetPropertyB;
    }

    public function setTargetPropertyC(string $value): void
    {
        $this->targetPropertyC = $value;
    }

    #[Map(property: 'sourcePropertyA')]
    public function getTargetPropertyC(): ?string
    {
        return $this->targetPropertyC;
    }
}
