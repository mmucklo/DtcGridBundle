<?php

namespace Dtc\GridBundle\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
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

    public $formatter;

    /**
     * Defaults to null
     * If there are ordered and null-annotated columns, null ones will appear last.
     *
     * @var int
     */
    public $order;
}
