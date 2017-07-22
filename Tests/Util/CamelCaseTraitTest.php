<?php

namespace Dtc\GridBundle\Tests\Util;

use Dtc\GridBundle\Util\CamelCaseTrait;
use PHPUnit\Framework\TestCase;

class CamelCaseTraitTest extends TestCase
{
    use CamelCaseTrait;

    public function testFromCamelCase()
    {
        self::assertEquals('Test Thing', $this->fromCamelCase('testThing'));
        self::assertEquals('Test', $this->fromCamelCase('test'));
        self::assertEquals('D T C', $this->fromCamelCase('DTC'));
    }
}
