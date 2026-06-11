<?php

namespace Dtc\GridBundle\Tests\Fixtures;

use Dtc\GridBundle\Annotation\Column;
use Dtc\GridBundle\Annotation\Grid;

/**
 * Fixture configured BOTH ways: PHP 8 attributes and Doctrine annotations,
 * with differing labels so tests can observe which source won. On PHP 8 the
 * attributes take precedence; on PHP 7 the attribute lines parse as comments
 * and the annotations are used.
 *
 * @Grid
 */
#[Grid]
class DualConfigGridEntity
{
    private $id;

    /**
     * @Column(label="Annotation Label")
     */
    #[Column(label: 'Attribute Label')]
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
