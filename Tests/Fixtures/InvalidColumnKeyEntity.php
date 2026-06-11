<?php

namespace Dtc\GridBundle\Tests\Fixtures;

use Dtc\GridBundle\Annotation\Column;
use Dtc\GridBundle\Annotation\Grid;

// Fixture with a misspelled Column key ("lable"). Reading it must fail
// loudly so the typo is caught, not silently fall back to a default label.
/**
 * @Grid
 */
class InvalidColumnKeyEntity
{
    private $id;

    /**
     * @Column(lable="Email Address")
     */
    private $name;
}
