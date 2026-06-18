<?php

namespace Dtc\GridBundle\Tests\Grid\Source;

use Doctrine\Common\Annotations\AnnotationReader;
use Dtc\GridBundle\Grid\Column\ActionGridColumn;
use Dtc\GridBundle\Tests\Fixtures\AnnotationGridAttributeActionsEntity;
use Dtc\GridBundle\Tests\Fixtures\HalfMigratedGridEntity;
use Dtc\GridBundle\Tests\Fixtures\MixedColumnsGridEntity;
use Dtc\GridBundle\Tests\Fixtures\ReverseHalfMigratedGridEntity;

/**
 * Partial-migration safety: converting only part of a class between annotation
 * and attribute form must not lose the configuration declared in the other
 * style — neither the class-level marker, nor individual columns, nor the
 * class-level actions/sort.
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

    public function testMixedAttributeAndAnnotationColumnsAcrossProperties()
    {
        $info = $this->buildColumnSourceInfo(MixedColumnsGridEntity::class, false, new AnnotationReader(), false);

        self::assertNotNull($info);
        self::assertSame('Attribute Name', $info->columns['name']->getLabel());
        self::assertSame('Annotation Email', $info->columns['email']->getLabel());
    }

    public function testAnnotationGridBorrowsClassLevelActionAndSortAttributes()
    {
        $info = $this->buildColumnSourceInfo(AnnotationGridAttributeActionsEntity::class, false, new AnnotationReader(), false);

        self::assertNotNull($info);
        self::assertSame(['name' => 'DESC'], $info->sort);
        $actionColumns = array_filter($info->columns, function ($column) {
            return $column instanceof ActionGridColumn;
        });
        self::assertCount(1, $actionColumns, 'Class-level #[ShowAction] must merge into the @Grid annotation marker');
    }
}
