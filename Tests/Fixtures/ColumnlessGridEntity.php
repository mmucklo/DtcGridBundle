<?php

namespace Dtc\GridBundle\Tests\Fixtures;

use Dtc\GridBundle\Annotation\Grid;

/**
 * Fixture with a grid marker but no column definitions, both ways: the
 * attribute form (read on PHP 8) and the annotation form (read on PHP 7,
 * where the attribute line parses as a comment). With reflection disallowed
 * this must produce a clear configuration error, not a poisoned cache file.
 *
 * @Grid
 */
#[Grid]
class ColumnlessGridEntity
{
    private $id;

    public function getId()
    {
        return $this->id;
    }
}
