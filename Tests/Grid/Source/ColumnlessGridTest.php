<?php

namespace Dtc\GridBundle\Tests\Grid\Source;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\ObjectManager;
use Dtc\GridBundle\Grid\Source\ColumnSource;
use Dtc\GridBundle\Tests\Fixtures\ColumnlessGridEntity;
use PHPUnit\Framework\TestCase;

/**
 * A Grid marker with no Column definitions and reflection disallowed used to
 * write a "return false" cache file and then throw "Bad column cache" — an
 * error that pointed at the cache instead of the misconfiguration, on every
 * request. It must instead fail with a clear configuration error before
 * anything is cached. The fixture carries both the attribute and annotation
 * forms, so the attribute path is exercised on PHP 8 and the annotation
 * path on PHP 7.
 */
class ColumnlessGridTest extends TestCase
{
    /** @var string */
    private $cacheDir;

    protected function setUp(): void
    {
        if (class_exists(AnnotationRegistry::class) && method_exists(AnnotationRegistry::class, 'registerLoader')) {
            AnnotationRegistry::registerLoader('class_exists');
        }
        $this->cacheDir = sys_get_temp_dir().'/dtc_grid_nocol_'.bin2hex(random_bytes(4));
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

    public function testGridWithoutColumnsThrowsClearConfigurationError()
    {
        $reflectionClass = new \ReflectionClass(ColumnlessGridEntity::class);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('getReflectionClass')->willReturn($reflectionClass);
        $metadata->method('getIdentifier')->willReturn(['id']);

        $factory = $this->createMock(ClassMetadataFactory::class);
        $factory->method('getMetadataFor')->willReturn($metadata);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('getMetadataFactory')->willReturn($factory);

        $columnSource = new ColumnSource($this->cacheDir, false);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('no Column definitions');
        $columnSource->getColumnSourceInfo($objectManager, ColumnlessGridEntity::class, false, new AnnotationReader());
    }
}
