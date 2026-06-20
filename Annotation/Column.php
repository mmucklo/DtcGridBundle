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
    use ValidatesArguments;

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
        self::assertString($label, 'label');
        self::assertBool($sortable, 'sortable');
        self::assertBool($searchable, 'searchable');
        self::assertString($formatter, 'formatter');
        self::assertInt($order, 'order');
        $this->label = $label;
        $this->sortable = $sortable;
        $this->searchable = $searchable;
        $this->formatter = $formatter;
        $this->order = $order;
    }
}
