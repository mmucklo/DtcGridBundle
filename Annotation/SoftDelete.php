<?php

namespace Dtc\GridBundle\Annotation;

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
class SoftDelete implements Annotation
{
    /**
     * @var string SoftDelete column in camel case (defaults to 'deletedAt')
     */
    public $column = 'deletedAt';

    /**
     * @var string Type of soft delete column - boolean, datetime, etc. (defaults to 'datetime')
     */
    public $type = 'datetime';
}