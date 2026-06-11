<?php

namespace Dtc\GridBundle\Tests\Fixtures;

use Dtc\GridBundle\Annotation\Column;
use Dtc\GridBundle\Annotation\Grid;

/**
 * Half-migrated fixture, the other direction: the class keeps the docblock
 * Grid marker while the properties already use PHP 8 Column attributes.
 *
 * @Grid
 */
class ReverseHalfMigratedGridEntity
{
    private $id;

    #[Column(label: 'Attribute Name Label', sortable: true)]
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
