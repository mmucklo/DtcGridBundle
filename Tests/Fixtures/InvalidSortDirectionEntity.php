<?php

namespace Dtc\GridBundle\Tests\Fixtures;

use Dtc\GridBundle\Annotation\Column;
use Dtc\GridBundle\Annotation\Grid;
use Dtc\GridBundle\Annotation\Sort;

// Invalid sort direction: must be rejected by ColumnSource at build time with
// the entity class name in the message, not by a context-free constructor.
#[Grid]
#[Sort(direction: 'UP', column: 'name')]
class InvalidSortDirectionEntity
{
    #[Column(label: 'Name')]
    private $name;

    public function getName()
    {
        return $this->name;
    }
}
