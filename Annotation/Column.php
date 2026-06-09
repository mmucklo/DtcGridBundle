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

    /**
     * @param array $data Doctrine annotation values (BC)
     */
    public function __construct(
        array $data = [],
        $label = null,
        $sortable = null,
        $searchable = null,
        $formatter = null,
        $order = null
    ) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        if (null !== $label) {
            $this->label = $label;
        }
        if (null !== $sortable) {
            $this->sortable = $sortable;
        }
        if (null !== $searchable) {
            $this->searchable = $searchable;
        }
        if (null !== $formatter) {
            $this->formatter = $formatter;
        }
        if (null !== $order) {
            $this->order = $order;
        }
    }
}
