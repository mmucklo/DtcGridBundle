<?php

namespace Dtc\GridBundle\Annotation;

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
class Sort implements Annotation
{
    /**
     * @var string Default sort order
     */
    public $direction = 'ASC';

    /**
     * @var string Column name to sort on by default
     */
    public $column;
}
