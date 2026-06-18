<?php

namespace Dtc\GridBundle\Tests\Fixtures;

use Dtc\GridBundle\Annotation\Column;
use Dtc\GridBundle\Annotation\Grid;

// Mid-migration fixture: under one #[Grid] marker, one property has been
// converted to a #[Column] attribute while another still uses the @Column
// docblock annotation. Both columns must survive (per-property merge).
#[Grid]
class MixedColumnsGridEntity
{
    private $id;

    #[Column(label: 'Attribute Name')]
    private $name;

    /**
     * @Column(label="Annotation Email")
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
