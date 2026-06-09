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

    /**
     * @param array $data Doctrine annotation values (BC)
     */
    public function __construct(
        array $data = [],
        $label = null,
        $route = null,
        $buttonClass = null,
        $onclick = null
    ) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
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
