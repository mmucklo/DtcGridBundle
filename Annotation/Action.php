<?php

namespace Dtc\Grid\Annotation;

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
class Action {
    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $route;
}