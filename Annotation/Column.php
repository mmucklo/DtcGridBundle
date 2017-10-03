<?php

namespace Dtc\GridBundle\Annotation;

/**
 * Class GridColumn.
 *
 * @Annotation
 * @Target("PROPERTY")
 */
class Column implements Annotation
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var bool
     */
    public $sortable = false;

    /**
     * @var bool
     */
    public $searchable = false;
}
