<?php

namespace Dtc\GridBundle\Tests\Php8Only;

use Dtc\GridBundle\Annotation\Column;
use Dtc\GridBundle\Annotation\Grid;
use Dtc\GridBundle\Annotation\Sort;
use PHPUnit\Framework\TestCase;

/**
 * Tests PHP 8 named-argument construction (simulates how #[Attribute] works).
 * This file uses PHP 8.0+ syntax and MUST NOT be loaded on PHP 7.x.
 */
class NamedArgAttributeTest extends TestCase
{
    public function testGridInstantiatesViaNamedArgs()
    {
        $grid = new Grid(actions: null, sort: new Sort(column: 'name', direction: 'ASC'));
        self::assertInstanceOf(Grid::class, $grid);
        self::assertInstanceOf(Sort::class, $grid->sort);
        self::assertSame('name', $grid->sort->column);
        self::assertSame('ASC', $grid->sort->direction);
    }

    public function testColumnInstantiatesViaNamedArgs()
    {
        $col = new Column(label: 'User Name', sortable: true, searchable: true, order: 3);
        self::assertSame('User Name', $col->label);
        self::assertTrue($col->sortable);
        self::assertTrue($col->searchable);
        self::assertSame(3, $col->order);
    }
}
