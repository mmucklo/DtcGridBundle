<?php

namespace Dtc\GridBundle\Tests\Fixtures;

use Dtc\GridBundle\Annotation\Column;
use Dtc\GridBundle\Annotation\Grid;

// Fixture with a wrongly typed Column value: sortable is a string, not a
// bool. Reading it must fail loudly, not silently produce a sortable column.
/**
 * @Grid
 */
class InvalidColumnTypeEntity
{
    private $id;

    /**
     * @Column(label="X", sortable="false")
     */
    private $name;
}
