<?php

namespace Dtc\GridBundle\Annotation;

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
class Action implements Annotation
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $route;
}
