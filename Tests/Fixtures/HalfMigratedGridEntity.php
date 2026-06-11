<?php

namespace Dtc\GridBundle\Tests\Fixtures;

use Dtc\GridBundle\Annotation\Column;
use Dtc\GridBundle\Annotation\Grid;

// Half-migrated fixture: the class-level marker was converted to the PHP 8
// attribute form, but the properties still carry docblock Column annotations.
// The attribute reader must fall back to the annotation columns instead of
// silently dropping the column configuration.
#[Grid]
class HalfMigratedGridEntity
{
    private $id;

    /**
     * @Column(label="Custom Name Label", sortable=true)
     */
    private $name;

    /**
     * @Column(label="Custom Email Label")
     */
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
