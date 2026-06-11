<?php

namespace Dtc\GridBundle\Tests\Grid\Source;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\ObjectManager;
use Dtc\GridBundle\Grid\Source\ColumnSource;
use PHPUnit\Framework\TestCase;

/**
 * Shared scaffolding for ColumnSource tests: a throwaway cache dir, the
 * doctrine/annotations 1.x loader bootstrap, and the ObjectManager mock
 * wiring needed to drive getColumnSourceInfo against a fixture class.
 */
abstract class ColumnSourceTestCase extends TestCase
{
    /** @var string */
    protected $cacheDir;

    protected function setUp(): void
    {
        // doctrine/annotations 1.x only autoloads annotation classes through
        // a registered loader; 2.x removed the registry and always autoloads.
        if (class_exists(AnnotationRegistry::class) && method_exists(AnnotationRegistry::class, 'registerLoader')) {
            AnnotationRegistry::registerLoader('class_exists');
        }
        $this->cacheDir = sys_get_temp_dir().'/dtc_grid_test_'.bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        self::removeDirectory($this->cacheDir);
    }

    protected static function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }

    /**
     * @param string $entityClass
     *
     * @return ObjectManager (mock) whose metadata factory resolves $entityClass
     */
    protected function createObjectManagerFor($entityClass)
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('getReflectionClass')->willReturn(new \ReflectionClass($entityClass));
        $metadata->method('getIdentifier')->willReturn(['id']);

        $factory = $this->createMock(ClassMetadataFactory::class);
        $factory->method('getMetadataFor')->willReturn($metadata);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('getMetadataFactory')->willReturn($factory);

        return $objectManager;
    }

    /**
     * Drive ColumnSource::getColumnSourceInfo against a fixture class.
     *
     * @param string $entityClass
     * @param bool   $debug
     * @param bool   $allowReflection
     *
     * @return \Dtc\GridBundle\Grid\Source\ColumnSourceInfo|null
     */
    protected function buildColumnSourceInfo($entityClass, $debug = false, ?Reader $reader = null, $allowReflection = true)
    {
        $columnSource = new ColumnSource($this->cacheDir, $debug);

        return $columnSource->getColumnSourceInfo($this->createObjectManagerFor($entityClass), $entityClass, $allowReflection, $reader);
    }
}
