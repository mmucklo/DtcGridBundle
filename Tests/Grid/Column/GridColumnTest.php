<?php

namespace Dtc\GridBundle\Tests\Grid\Column;

use Dtc\GridBundle\Grid\Column\GridColumn;
use Dtc\GridBundle\Grid\Source\GridSourceInterface;
use PHPUnit\Framework\TestCase;

class GridColumnTest extends TestCase
{
    public function testDefaults()
    {
        $column = new GridColumn('firstName');
        self::assertSame('firstName', $column->getField());
        self::assertSame('FirstName', $column->getLabel());
        self::assertTrue($column->isSearchable());
        self::assertNull($column->getOrder());
        self::assertSame([], $column->getOptions());
    }

    public function testCustomLabelAndOptions()
    {
        $column = new GridColumn('email', 'E-mail', null, ['sortable' => true], false, 5);
        self::assertSame('E-mail', $column->getLabel());
        self::assertFalse($column->isSearchable());
        self::assertSame(5, $column->getOrder());
        self::assertSame(['sortable' => true], $column->getOptions());
        self::assertTrue($column->getOption('sortable'));
        self::assertNull($column->getOption('missing'));
    }

    public function testInvalidOrderThrows()
    {
        $this->expectException(\InvalidArgumentException::class);
        new GridColumn('foo', null, null, null, true, 'not-an-int');
    }

    public function testFormatScalarField()
    {
        $column = new GridColumn('name');
        $gridSource = $this->createMock(GridSourceInterface::class);
        self::assertSame('Ada', $column->format(['name' => 'Ada'], $gridSource));
    }

    public function testFormatObjectWithGetter()
    {
        $column = new GridColumn('Name');
        $gridSource = $this->createMock(GridSourceInterface::class);
        $obj = new class {
            public function getName()
            {
                return 'Grace';
            }
        };
        self::assertSame('Grace', $column->format($obj, $gridSource));
    }

    public function testFormatDateTimeReturnsIso8601()
    {
        $column = new GridColumn('When');
        $gridSource = $this->createMock(GridSourceInterface::class);
        $dt = new \DateTime('2026-01-02T03:04:05+00:00');
        $obj = new class($dt) {
            private $when;

            public function __construct($when)
            {
                $this->when = $when;
            }

            public function getWhen()
            {
                return $this->when;
            }
        };
        self::assertSame($dt->format(\DateTime::ISO8601), $column->format($obj, $gridSource));
    }

    public function testCustomFormatterIsInvoked()
    {
        $formatter = function ($object, GridColumn $column) {
            return strtoupper($object['name']);
        };
        $column = new GridColumn('name', null, $formatter);
        $gridSource = $this->createMock(GridSourceInterface::class);
        self::assertSame('ADA', $column->format(['name' => 'ada'], $gridSource));
        self::assertSame($formatter, $column->getFormatter());
    }
}
