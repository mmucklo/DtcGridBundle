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
}
