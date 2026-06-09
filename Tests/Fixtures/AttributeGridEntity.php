<?php

namespace Dtc\GridBundle\Tests\Fixtures;

use Dtc\GridBundle\Annotation\Column;
use Dtc\GridBundle\Annotation\DeleteAction;
use Dtc\GridBundle\Annotation\Grid;
use Dtc\GridBundle\Annotation\ShowAction;
use Dtc\GridBundle\Annotation\Sort;

/**
 * Fixture exercising the PHP 8 attribute reader: #[Grid] on the class,
 * #[Column] on properties, plus class-level #[ShowAction], #[DeleteAction]
 * and #[Sort]. On PHP 7 the attributes are parsed as comments and ignored.
 */
#[Grid]
#[ShowAction]
#[DeleteAction]
#[Sort(column: 'name', direction: 'ASC')]
class AttributeGridEntity
{
    private $id;

    #[Column(label: 'Full Name', sortable: true, searchable: true)]
    private $name;

    #[Column(label: 'Email Address', sortable: true)]
    private $email;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }
}
