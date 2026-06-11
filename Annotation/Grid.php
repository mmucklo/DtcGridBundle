<?php

namespace Dtc\GridBundle\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Grid implements Annotation
{
    /**
     * @var array<Action>
     */
    public $actions;

    /**
     * @var Sort
     */
    public $sort;

    /**
     * @var array<Sort>
     */
    public $sortMulti;

    /**
     * Actions and sort cannot be passed as attribute arguments (PHP attribute
     * arguments must be constant expressions) — use the class-level
     * #[ShowAction]/#[DeleteAction]/#[Action]/#[Sort] attributes instead.
     * The array form exists for the Doctrine annotation reader, which passes
     * the parsed @Grid values as a single array.
     *
     * @param array $data Doctrine annotation values
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
