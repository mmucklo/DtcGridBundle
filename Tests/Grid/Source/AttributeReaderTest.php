<?php

namespace Dtc\GridBundle\Tests\Grid\Source;

use Dtc\GridBundle\Annotation\Column;
use Dtc\GridBundle\Annotation\Grid;
use Dtc\GridBundle\Annotation\Sort;
use PHPUnit\Framework\TestCase;

/**
 * Tests annotation class constructors with Doctrine-style $data array (PHP 7.2+).
 */
class AttributeReaderTest extends TestCase
{
    public function testColumnDoctrineCompatArray()
    {
        $col = new Column(['label' => 'Email', 'sortable' => true]);
        self::assertSame('Email', $col->label);
        self::assertTrue($col->sortable);
        self::assertFalse($col->searchable);
    }

    public function testGridDoctrineCompatArray()
    {
        $sort = new Sort(['column' => 'id', 'direction' => 'DESC']);
        $grid = new Grid(['sort' => $sort]);
        self::assertSame($sort, $grid->sort);
        self::assertNull($grid->actions);
    }

    public function testSortDoctrineCompatArray()
    {
        $sort = new Sort(['column' => 'name', 'direction' => 'ASC']);
        self::assertSame('name', $sort->column);
        self::assertSame('ASC', $sort->direction);
    }

    public function testColumnPositionalDefaults()
    {
        $col = new Column();
        self::assertNull($col->label);
        self::assertFalse($col->sortable);
        self::assertFalse($col->searchable);
        self::assertNull($col->formatter);
        self::assertNull($col->order);
    }

    public function testGridEmptyDefaults()
    {
        $grid = new Grid();
        self::assertNull($grid->actions);
        self::assertNull($grid->sort);
        self::assertNull($grid->sortMulti);
    }
}
