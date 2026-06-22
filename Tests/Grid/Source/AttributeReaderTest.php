<?php

namespace Dtc\GridBundle\Tests\Grid\Source;

use Dtc\GridBundle\Annotation\Action;
use Dtc\GridBundle\Annotation\Column;
use Dtc\GridBundle\Annotation\Grid;
use Dtc\GridBundle\Annotation\ShowAction;
use Dtc\GridBundle\Annotation\Sort;
use PHPUnit\Framework\TestCase;

/**
 * Tests the annotation/attribute constructor contract (PHP 7.2+ positional
 * syntax): natural parameters, normalization, and argument validation.
 */
class AttributeReaderTest extends TestCase
{
    public function testColumnPositional()
    {
        $col = new Column('Email', true);
        self::assertSame('Email', $col->label);
        self::assertTrue($col->sortable);
        self::assertFalse($col->searchable);
    }

    public function testColumnRejectsStringSortable()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"sortable" must be a bool');
        new Column('Email', 'false');
    }

    public function testColumnRejectsNonIntOrder()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"order" must be an int');
        new Column('Email', false, false, null, 'first');
    }

    public function testColumnAcceptsStringAndArrayCallableFormatter()
    {
        // formatter is a callable: a string ('Class::method') or an array
        // (['Class', 'method']) — both must be accepted, not type-rejected.
        $stringFormatter = new Column('Price', false, false, 'App\\Formatter::price');
        self::assertSame('App\\Formatter::price', $stringFormatter->formatter);

        $arrayFormatter = new Column('Price', false, false, ['App\\Formatter', 'price']);
        self::assertSame(['App\\Formatter', 'price'], $arrayFormatter->formatter);
    }

    public function testSortRejectsInvalidDirection()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"direction" must be one of ASC, DESC');
        new Sort('UP');
    }

    public function testActionRejectsNonStringLabel()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"label" must be a string');
        new Action(new \stdClass());
    }

    public function testGridNormalizesSingleActionToArray()
    {
        $grid = new Grid(new ShowAction());
        self::assertIsArray($grid->actions);
        self::assertCount(1, $grid->actions);
        self::assertInstanceOf(ShowAction::class, $grid->actions[0]);
    }

    public function testGridRejectsNonActionElements()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"actions" elements must be Action instances');
        new Grid(['show']);
    }

    public function testGridRejectsNonSort()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"sort" must be a Sort instance');
        new Grid(null, 'name');
    }

    public function testGridSortAndSortMulti()
    {
        $sort = new Sort('DESC', 'id');
        $grid = new Grid(null, $sort);
        self::assertSame($sort, $grid->sort);
        self::assertNull($grid->actions);

        $gridMulti = new Grid(null, null, [$sort, new Sort('ASC', 'name')]);
        self::assertCount(2, $gridMulti->sortMulti);
    }

    public function testSortPositional()
    {
        $sort = new Sort('ASC', 'name');
        self::assertSame('name', $sort->column);
        self::assertSame('ASC', $sort->direction);
    }

    public function testColumnDefaults()
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

    public function testShowActionDefaultsSurviveConstruction()
    {
        $action = new ShowAction();
        self::assertSame('Show', $action->label);
        self::assertSame('dtc_grid_show', $action->route);

        $custom = new ShowAction('View');
        self::assertSame('View', $custom->label);
        self::assertSame('dtc_grid_show', $custom->route);
    }
}
