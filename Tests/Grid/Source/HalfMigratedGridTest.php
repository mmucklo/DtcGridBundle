<?php

namespace Dtc\GridBundle\Tests\Grid\Source;

use Doctrine\Common\Annotations\AnnotationReader;
use Dtc\GridBundle\Tests\Fixtures\HalfMigratedGridEntity;
use Dtc\GridBundle\Tests\Fixtures\ReverseHalfMigratedGridEntity;

/**
 * Partial-migration safety: converting only the class-level Grid marker
 * between annotation and attribute form must not lose the column
 * configuration declared in the other style. Regression test for the
 * attribute path short-circuiting the annotation columns.
 *
 * @requires PHP 8.0
 */
class HalfMigratedGridTest extends ColumnSourceTestCase
{
    public function testAttributeGridWithAnnotationColumns()
    {
        $info = $this->buildColumnSourceInfo(HalfMigratedGridEntity::class, false, new AnnotationReader(), false);

        self::assertNotNull($info);
        self::assertSame('Custom Name Label', $info->columns['name']->getLabel());
        self::assertSame('Custom Email Label', $info->columns['email']->getLabel());
    }

    public function testAnnotationGridWithAttributeColumns()
    {
        $info = $this->buildColumnSourceInfo(ReverseHalfMigratedGridEntity::class, false, new AnnotationReader(), false);

        self::assertNotNull($info);
        self::assertSame('Attribute Name Label', $info->columns['name']->getLabel());
    }
}
