<?php

namespace Dtc\GridBundle\Annotation;

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
class ShowAction extends Action
{
    /**
     * @var string
     */
    public $label = 'Show';

    /**
     * @var string
     */
    public $route = 'dtc_grid_show';
}
