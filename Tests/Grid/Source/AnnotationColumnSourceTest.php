<?php

namespace Dtc\GridBundle\Tests\Grid\Source;

use Doctrine\Common\Annotations\AnnotationReader;
use Dtc\GridBundle\Grid\Column\ActionGridColumn;
use Dtc\GridBundle\Grid\Column\GridColumn;
use Dtc\GridBundle\Tests\Fixtures\AnnotatedGridEntity;

/**
 * Drives the real Doctrine annotation reader path (getColumnSourceInfo ->
 * readGridAnnotations -> buildColumnInfoFromGrid) against a fixture entity.
 * This is the bundle's original public API: it must keep working on every
 * supported doctrine/annotations version.
 */
class AnnotationColumnSourceTest extends ColumnSourceTestCase
{
    public function testReadsColumnsFromAnnotations()
    {
        $info = $this->buildColumnSourceInfo(AnnotatedGridEntity::class, false, new AnnotationReader());

        $columns = $info->columns;
        self::assertArrayHasKey('name', $columns);
        self::assertArrayHasKey('email', $columns);
        self::assertInstanceOf(GridColumn::class, $columns['name']);
        self::assertSame('Full Name', $columns['name']->getLabel());
        self::assertSame('Email Address', $columns['email']->getLabel());
    }

    public function testActionColumnResolvedFromNestedAnnotations()
    {
        $info = $this->buildColumnSourceInfo(AnnotatedGridEntity::class, false, new AnnotationReader());

        $actionColumns = array_filter($info->columns, function ($column) {
            return $column instanceof ActionGridColumn;
        });
        self::assertCount(1, $actionColumns, 'Expected a single action column from nested @ShowAction + @DeleteAction');
    }

    public function testSortResolvedFromNestedAnnotation()
    {
        $info = $this->buildColumnSourceInfo(AnnotatedGridEntity::class, false, new AnnotationReader());

        self::assertSame(['name' => 'ASC'], $info->sort);
    }

    public function testDebugModeResolvesAnnotations()
    {
        $info = $this->buildColumnSourceInfo(AnnotatedGridEntity::class, true, new AnnotationReader());

        self::assertArrayHasKey('name', $info->columns);
        self::assertSame('Full Name', $info->columns['name']->getLabel());
        self::assertSame(['name' => 'ASC'], $info->sort);
    }
}
