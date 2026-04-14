<?php

namespace Dtc\GridBundle\Tests\Annotation;

use Dtc\GridBundle\Annotation\Action;
use Dtc\GridBundle\Annotation\Column;
use Dtc\GridBundle\Annotation\DeleteAction;
use Dtc\GridBundle\Annotation\Grid;
use Dtc\GridBundle\Annotation\ShowAction;
use Dtc\GridBundle\Annotation\Sort;
use PHPUnit\Framework\TestCase;

/**
 * @requires PHP 8.0
 */
class AttributeMarkerTest extends TestCase
{
    public function provideAttributeClasses(): array
    {
        return [
            [Grid::class],
            [Column::class],
            [Sort::class],
            [Action::class],
            [ShowAction::class],
            [DeleteAction::class],
        ];
    }

    /**
     * @dataProvider provideAttributeClasses
     */
    public function testClassIsMarkedAsPhp8Attribute(string $class)
    {
        $reflection = new \ReflectionClass($class);
        $attributes = $reflection->getAttributes(\Attribute::class);
        self::assertCount(1, $attributes, "$class should carry #[\\Attribute] marker");
    }
}
