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
}
