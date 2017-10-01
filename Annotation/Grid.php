<?php

namespace Dtc\GridBundle\Annotation;

/**
 * Class GridColumn.
 *
 * @Annotation
 * @Target("CLASS")
 */
class Grid
{
    /**
     * @var array<\Dtc\Grid\GridAction>
     */
    public $actions;
}