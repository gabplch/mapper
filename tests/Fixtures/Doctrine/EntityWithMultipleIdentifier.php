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

namespace Rekalogika\Mapper\Tests\Fixtures\Doctrine;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class EntityWithMultipleIdentifier
{
    #[ORM\Id]
    #[ORM\Column(name: 'id1', type: 'string', length: 255)]
    private string $id1;

    #[ORM\Id]
    #[ORM\Column(name: 'id2', type: 'string', length: 255)]
    private string $id2;

    #[ORM\Column]
    private string $name;

    public function __construct(string $id1, string $id2, string $name)
    {
        $this->id1 = $id1;
        $this->id2 = $id2;
        $this->name = $name;
    }

    public function getId1(): string
    {
        return $this->id1;
    }

    public function getId2(): string
    {
        return $this->id2;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
