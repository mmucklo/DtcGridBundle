<?php

namespace Dtc\GridBundle\Annotation;

/**
 * @Annotation
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
     * @param array $data Doctrine annotation values (BC)
     */
    public function __construct(array $data = [], $direction = null, $column = null)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        if (null !== $direction) {
            $this->direction = $direction;
        }
        if (null !== $column) {
            $this->column = $column;
        }
    }
}
