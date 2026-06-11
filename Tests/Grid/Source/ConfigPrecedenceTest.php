<?php

namespace Dtc\GridBundle\Tests\Grid\Source;

use Doctrine\Common\Annotations\AnnotationReader;
use Dtc\GridBundle\Tests\Fixtures\DualConfigGridEntity;

/**
 * Pins the precedence contract: when a class carries both PHP 8 attributes
 * and Doctrine annotations, the attributes win and the annotations are
 * silently ignored, even with an annotation reader available.
 *
 * @requires PHP 8.0
 */
class ConfigPrecedenceTest extends ColumnSourceTestCase
{
    public function testAttributesWinOverAnnotationsWhenBothPresent()
    {
        $info = $this->buildColumnSourceInfo(DualConfigGridEntity::class, false, new AnnotationReader());

        self::assertSame('Attribute Label', $info->columns['name']->getLabel());
    }
}
