<?php

namespace Dtc\GridBundle\Tests\DependencyInjection;

use Dtc\GridBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testDefaults()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [[]]);

        self::assertArrayHasKey('reflection', $config);
        self::assertArrayHasKey('jquery', $config);
        self::assertArrayHasKey('datatables', $config);
        self::assertArrayHasKey('theme', $config);
        self::assertNull($config['reflection']['allowed_entities']);
        self::assertStringStartsWith('//code.jquery.com/', $config['jquery']['url']);
    }

    public function testAllowedEntitiesArray()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [[
            'reflection' => ['allowed_entities' => ['App\\Entity\\Foo']],
        ]]);

        self::assertSame(['App\\Entity\\Foo'], $config['reflection']['allowed_entities']);
    }

    public function testAllowedEntitiesWildcard()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [[
            'reflection' => ['allowed_entities' => '*'],
        ]]);

        self::assertSame('*', $config['reflection']['allowed_entities']);
    }
}
