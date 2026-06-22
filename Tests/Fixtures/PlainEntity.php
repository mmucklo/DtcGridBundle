<?php

namespace Dtc\GridBundle\Tests\Fixtures;

/**
 * Fixture with no grid configuration at all — stands in for an entity whose
 * grid is configured externally (dtc_grid YAML files), where the column
 * cache is written at container compile time.
 */
class PlainEntity
{
    private $id;

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
