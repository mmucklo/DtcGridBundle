<?php

namespace Dtc\GridBundle\Tests\Grid\Source;

use Dtc\GridBundle\Grid\Column\ActionGridColumn;
use Dtc\GridBundle\Grid\Column\GridColumn;
use Dtc\GridBundle\Tests\Fixtures\AttributeGridEntity;

/**
 * Drives the real attribute-reading path (getColumnSourceInfo ->
 * collectGrid/collectColumns -> buildColumnInfoFromGrid) against a fixture,
 * asserting columns, actions and sort all resolve from attributes.
 *
 * @requires PHP 8.0
 */
class AttributeColumnSourceTest extends ColumnSourceTestCase
{
    public function testReadsColumnsActionsAndSortFromAttributes()
    {
        $info = $this->buildColumnSourceInfo(AttributeGridEntity::class);

        $columns = $info->columns;
        self::assertArrayHasKey('name', $columns);
        self::assertArrayHasKey('email', $columns);
        self::assertInstanceOf(GridColumn::class, $columns['name']);
        self::assertSame('Full Name', $columns['name']->getLabel());
        self::assertSame('Email Address', $columns['email']->getLabel());
    }

    public function testActionColumnResolvedFromClassAttributes()
    {
        $info = $this->buildColumnSourceInfo(AttributeGridEntity::class);

        $actionColumns = array_filter($info->columns, function ($column) {
            return $column instanceof ActionGridColumn;
        });
        self::assertCount(1, $actionColumns, 'Expected a single action column from #[ShowAction] + #[DeleteAction]');
    }

    public function testSortResolvedFromClassAttribute()
    {
        $info = $this->buildColumnSourceInfo(AttributeGridEntity::class);

        self::assertSame(['name' => 'ASC'], $info->sort);
    }

    public function testDebugModeResolvesAttributesWithoutReader()
    {
        // In debug mode with no annotation reader, the freshly built
        // attribute config must be served and cached without a hiccup.
        $info = $this->buildColumnSourceInfo(AttributeGridEntity::class, true);

        self::assertArrayHasKey('name', $info->columns);
        self::assertSame('Full Name', $info->columns['name']->getLabel());
        self::assertSame(['name' => 'ASC'], $info->sort);
    }
}
