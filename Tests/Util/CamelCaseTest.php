<?php

namespace Dtc\GridBundle\Tests\Util;

use Dtc\GridBundle\Util\CamelCase;
use PHPUnit\Framework\TestCase;

class CamelCaseTest extends TestCase
{
    public function testFromCamelCase()
    {
        self::assertEquals('Test Thing', CamelCase::fromCamelCase('testThing'));
        self::assertEquals('Test', CamelCase::fromCamelCase('test'));
        self::assertEquals('D T C', CamelCase::fromCamelCase('DTC'));
    }
}
