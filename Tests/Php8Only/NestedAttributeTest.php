<?php

namespace Dtc\GridBundle\Tests\Php8Only;

use Dtc\GridBundle\Grid\Column\ActionGridColumn;
use Dtc\GridBundle\Tests\Grid\Source\ColumnSourceTestCase;

/**
 * On PHP 8.1+ actions and sort can be nested directly inside #[Grid] via
 * new-in-initializers. The fixture class must only be loaded under the
 * version gate — `new` in attribute arguments is a compile error on 8.0.
 */
class NestedAttributeTest extends ColumnSourceTestCase
{
    /**
     * @requires PHP 8.1
     */
    public function testNestedActionsAndSortInsideGridAttribute()
    {
        $entityClass = 'Dtc\GridBundle\Tests\Fixtures\NestedActionsGridEntity';
        $info = $this->buildColumnSourceInfo($entityClass);

        self::assertSame('Full Name', $info->columns['name']->getLabel());
        self::assertSame(['name' => 'DESC'], $info->sort);

        $actionColumns = array_values(array_filter($info->columns, function ($column) {
            return $column instanceof ActionGridColumn;
        }));
        self::assertCount(1, $actionColumns);
    }
}
