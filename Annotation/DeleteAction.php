<?php

namespace Dtc\GridBundle\Annotation;

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
class ShowAction extends Action {
    /**
     * @var string
     */
    public $label = 'Delete';

    /**
     * @var \Dtc\GridBundle\Annotation\SoftDelete
     */
    public $softDelete;

    /**
     * @var string
     */
    public $route = 'dtc_grid_grid_delete';
}