<?php

namespace Dtc\GridBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * @Annotation
 * @NamedArgumentConstructor
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

    public function __construct($label = null, $sortable = false, $searchable = false, $formatter = null, $order = null)
    {
        if (null !== $label && !is_string($label)) {
            throw new \InvalidArgumentException('Column "label" must be a string, got '.gettype($label));
        }
        if (!is_bool($sortable)) {
            throw new \InvalidArgumentException('Column "sortable" must be a bool, got '.gettype($sortable));
        }
        if (!is_bool($searchable)) {
            throw new \InvalidArgumentException('Column "searchable" must be a bool, got '.gettype($searchable));
        }
        if (null !== $order && !is_int($order)) {
            throw new \InvalidArgumentException('Column "order" must be an int, got '.gettype($order));
        }
        $this->label = $label;
        $this->sortable = $sortable;
        $this->searchable = $searchable;
        $this->formatter = $formatter;
        $this->order = $order;
    }
}
