<?php

namespace Dtc\GridBundle\Tests\Fixtures;

use Dtc\GridBundle\Annotation\Column;
use Dtc\GridBundle\Annotation\DeleteAction;
use Dtc\GridBundle\Annotation\Grid;
use Dtc\GridBundle\Annotation\ShowAction;
use Dtc\GridBundle\Annotation\Sort;

/**
 * Fixture nesting actions and sort directly inside #[Grid], legal since
 * PHP 8.1 (new-in-initializers). MUST NOT be class-loaded on PHP 8.0 or
 * lower — `new` in attribute arguments is a compile-time error there — so
 * reference it only from tests gated with `requires PHP 8.1`.
 */
#[Grid(actions: [new ShowAction(label: 'View'), new DeleteAction()], sort: new Sort(column: 'name', direction: 'DESC'))]
class NestedActionsGridEntity
{
    private $id;

    #[Column(label: 'Full Name', sortable: true)]
    private $name;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
}
