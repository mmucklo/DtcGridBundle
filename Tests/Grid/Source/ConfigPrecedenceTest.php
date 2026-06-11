<?php

namespace Dtc\GridBundle\Tests\Grid\Source;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\ObjectManager;
use Dtc\GridBundle\Grid\Source\ColumnSource;
use Dtc\GridBundle\Tests\Fixtures\DualConfigGridEntity;
use PHPUnit\Framework\TestCase;

/**
 * Pins the precedence contract: when a class carries both PHP 8 attributes
 * and Doctrine annotations, the attributes win and the annotations are
 * silently ignored, even with an annotation reader available.
 *
 * @requires PHP 8.0
 */
class ConfigPrecedenceTest extends TestCase
{
    /** @var string */
    private $cacheDir;

    protected function setUp(): void
    {
        if (class_exists(AnnotationRegistry::class) && method_exists(AnnotationRegistry::class, 'registerLoader')) {
            AnnotationRegistry::registerLoader('class_exists');
        }
        $this->cacheDir = sys_get_temp_dir().'/dtc_grid_prec_'.bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        if (!is_dir($this->cacheDir)) {
            return;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($this->cacheDir);
    }

    public function testAttributesWinOverAnnotationsWhenBothPresent()
    {
        $reflectionClass = new \ReflectionClass(DualConfigGridEntity::class);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('getReflectionClass')->willReturn($reflectionClass);
        $metadata->method('getIdentifier')->willReturn(['id']);

        $factory = $this->createMock(ClassMetadataFactory::class);
        $factory->method('getMetadataFor')->willReturn($metadata);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('getMetadataFactory')->willReturn($factory);

        $columnSource = new ColumnSource($this->cacheDir, false);
        $info = $columnSource->getColumnSourceInfo($objectManager, DualConfigGridEntity::class, true, new AnnotationReader());

        self::assertSame('Attribute Label', $info->columns['name']->getLabel());
    }
}
