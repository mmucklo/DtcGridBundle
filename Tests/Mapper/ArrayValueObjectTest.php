<?php

namespace Dtc\GridBundle\Tests\Mapper;

use Dtc\GridBundle\Mapper\ArrayValueObject;
use PHPUnit\Framework\TestCase;

class ArrayValueObjectTest extends TestCase
{
    public function testGetValueTopLevel()
    {
        $avo = new ArrayValueObject(['name' => 'Ada']);
        self::assertSame('Ada', $avo->getValue('name'));
    }

    public function testGetValueNested()
    {
        $avo = new ArrayValueObject(['user' => ['profile' => ['email' => 'a@b.c']]]);
        self::assertSame('a@b.c', $avo->getValue('user', 'profile', 'email'));
    }

    public function testMissingKeyReturnsNull()
    {
        $avo = new ArrayValueObject(['name' => 'Ada']);
        self::assertNull($avo->getValue('missing'));
        self::assertNull($avo->getValue('name', 'nested'));
    }

    public function testEmptyArgsThrows()
    {
        $avo = new ArrayValueObject([]);
        $this->expectException(\Exception::class);
        $avo->getValueByArray([]);
    }

    public function testNullArgThrows()
    {
        $avo = new ArrayValueObject(['a' => 1]);
        $this->expectException(\Exception::class);
        $avo->getValueByArray([null]);
    }

    public function testNonScalarArgThrows()
    {
        $avo = new ArrayValueObject(['a' => 1]);
        $this->expectException(\Exception::class);
        $avo->getValueByArray([['array' => 'key']]);
    }
}
