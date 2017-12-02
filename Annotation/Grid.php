<?php

namespace Dtc\GridBundle\Annotation;

/**
 * Class GridColumn.
 *
 * @Annotation
 * @Target("CLASS")
 */
class Grid implements Annotation
{
    /**
     * @var array<\Dtc\GridBundle\Annotation\Action>
     */
    public $actions;

    /**
     * @var \Dtc\GridBundle\Annotation\Sort
     */
    public $sort;
}
