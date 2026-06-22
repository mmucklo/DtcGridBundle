<?php

namespace Dtc\GridBundle\Tests\Fixtures;

use Dtc\GridBundle\Annotation\Column;
use Dtc\GridBundle\Annotation\Grid;
use Dtc\GridBundle\Annotation\ShowAction;
use Dtc\GridBundle\Annotation\Sort;

// Mid-migration fixture: the class keeps the annotation Grid marker and an
// annotated column property, while actions and sort have moved to the new
// class-level PHP 8 attributes. The attribute action/sort must merge into the
// annotation-derived Grid marker rather than being silently dropped. (Prose
// avoids leading at-signs so the DocParser sees only the real @Grid below.)
/**
 * @Grid
 */
#[ShowAction]
#[Sort(column: 'name', direction: 'DESC')]
class AnnotationGridAttributeActionsEntity
{
    private $id;

    /**
     * @Column(label="Name")
     */
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
