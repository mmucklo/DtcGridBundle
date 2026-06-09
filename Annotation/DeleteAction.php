<?php

namespace Dtc\GridBundle\Annotation;

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class DeleteAction extends Action
{
    /**
     * @var string
     */
    public $label = 'Delete';

    /**
     * @var string
     */
    public $route = 'dtc_grid_delete';

    public function __construct(
        array $data = [],
        $label = null,
        $route = null,
        $buttonClass = null,
        $onclick = null
    ) {
        parent::__construct($data, $label, $route, $buttonClass, $onclick);
        if (null === $this->label) {
            $this->label = 'Delete';
        }
        if (null === $this->route) {
            $this->route = 'dtc_grid_delete';
        }
    }
}
