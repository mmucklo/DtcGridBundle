<?php

namespace Dtc\GridBundle\Tests\Fixtures;

use Dtc\GridBundle\Annotation\Column;
use Dtc\GridBundle\Annotation\Grid;

// An explicit empty actions list must not produce a stray (empty) action
// column — it should behave like "no actions".
#[Grid(actions: [])]
class EmptyActionsGridEntity
{
    #[Column(label: 'Name')]
    private $name;

    public function getName()
    {
        return $this->name;
    }
}
