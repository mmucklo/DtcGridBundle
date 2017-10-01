<?php

namespace Dtc\Grid\Annotation;

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
class ShowAction extends Action {
    /**
     * @var string
     */
    public $label = 'Show';

    /**
     * @var string
     */
    public $route = 'dtc_grid_grid_show';
}