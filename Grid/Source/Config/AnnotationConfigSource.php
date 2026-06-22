<?php

namespace Dtc\GridBundle\Grid\Source\Config;

use Doctrine\Common\Annotations\Reader;

/**
 * Reads grid configuration from Doctrine annotations. Actions and sort are
 * nested inside the @Grid annotation itself, so this source declares none
 * separately (getActions/getSorts are empty).
 */
class AnnotationConfigSource implements GridConfigSourceInterface
{
    /** @var Reader */
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function getGrid(\ReflectionClass $reflectionClass)
    {
        return $this->reader->getClassAnnotation($reflectionClass, 'Dtc\GridBundle\Annotation\Grid');
    }

    public function getColumn(\ReflectionProperty $property)
    {
        return $this->reader->getPropertyAnnotation($property, 'Dtc\GridBundle\Annotation\Column');
    }

    public function getActions(\ReflectionClass $reflectionClass)
    {
        return null;
    }

    public function getSorts(\ReflectionClass $reflectionClass)
    {
        return [];
    }
}
