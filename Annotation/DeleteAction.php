<?php

namespace Dtc\GridBundle\Annotation;

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
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
