<?php

namespace Dtc\GridBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target("ANNOTATION")
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class Sort implements Annotation
{
    /**
     * @var string Default sort order
     */
    public $direction = 'ASC';

    /**
     * @var string Column name to sort on by default
     */
    public $column;

    /**
     * Direction and column are validated by ColumnSource at build time, where
     * the column list and the entity class name are available — so an invalid
     * direction or unknown column is reported against the entity, not as a
     * context-free constructor error.
     */
    public function __construct($direction = null, $column = null)
    {
        // Only assign non-null values so the 'ASC' direction default survives
        // an omitted argument.
        if (null !== $direction) {
            $this->direction = $direction;
        }
        if (null !== $column) {
            $this->column = $column;
        }
    }
}
