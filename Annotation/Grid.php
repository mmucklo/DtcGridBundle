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
     * @param array $data Doctrine annotation values (BC)
     */
    public function __construct(
        array $data = [],
        $actions = null,
        $sort = null,
        $sortMulti = null
    ) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        if (null !== $actions) {
            $this->actions = $actions;
        }
        if (null !== $sort) {
            $this->sort = $sort;
        }
        if (null !== $sortMulti) {
            $this->sortMulti = $sortMulti;
        }
    }
}
