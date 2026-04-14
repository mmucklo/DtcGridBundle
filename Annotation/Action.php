<?php

namespace Dtc\GridBundle\Annotation;

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
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

    /**
     * @var string
     */
    public $buttonClass;

    /**
     * @var string
     */
    public $onclick;
}
