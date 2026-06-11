<?php

namespace Dtc\GridBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * @Annotation
 * @NamedArgumentConstructor
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

    public function __construct($label = null, $route = null, $buttonClass = null, $onclick = null)
    {
        // Only assign non-null values so subclass property defaults
        // (ShowAction/DeleteAction labels and routes) survive omitted args.
        if (null !== $label) {
            $this->label = $label;
        }
        if (null !== $route) {
            $this->route = $route;
        }
        if (null !== $buttonClass) {
            $this->buttonClass = $buttonClass;
        }
        if (null !== $onclick) {
            $this->onclick = $onclick;
        }
    }
}
