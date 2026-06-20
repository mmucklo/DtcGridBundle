<?php

namespace Dtc\GridBundle\Grid\Source\Config;

use Dtc\GridBundle\Annotation\Action;
use Dtc\GridBundle\Annotation\Column;
use Dtc\GridBundle\Annotation\Grid;
use Dtc\GridBundle\Annotation\Sort;

/**
 * Reads grid configuration from native PHP 8 attributes. Only instantiated on
 * PHP 8.0+, so the ReflectionAttribute API it relies on is always available
 * when its methods run.
 */
class AttributeConfigSource implements GridConfigSourceInterface
{
    public function getGrid(\ReflectionClass $reflectionClass)
    {
        $gridAttrs = $reflectionClass->getAttributes(Grid::class);

        return empty($gridAttrs) ? null : $gridAttrs[0]->newInstance();
    }

    public function getColumn(\ReflectionProperty $property)
    {
        $colAttrs = $property->getAttributes(Column::class);

        return empty($colAttrs) ? null : $colAttrs[0]->newInstance();
    }

    public function getActions(\ReflectionClass $reflectionClass)
    {
        // IS_INSTANCEOF so subclasses (#[ShowAction], #[DeleteAction]) and
        // custom #[Action] subclasses are all collected.
        $actionAttrs = $reflectionClass->getAttributes(Action::class, \ReflectionAttribute::IS_INSTANCEOF);
        if (empty($actionAttrs)) {
            return null;
        }

        return array_map(function ($attr) {
            return $attr->newInstance();
        }, $actionAttrs);
    }

    public function getSorts(\ReflectionClass $reflectionClass)
    {
        $sortAttrs = $reflectionClass->getAttributes(Sort::class);

        return array_map(function ($attr) {
            return $attr->newInstance();
        }, $sortAttrs);
    }
}
