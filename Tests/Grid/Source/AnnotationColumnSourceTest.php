<?php

namespace Dtc\GridBundle\Tests\Grid\Source;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\ObjectManager;
use Dtc\GridBundle\Grid\Column\ActionGridColumn;
use Dtc\GridBundle\Grid\Column\GridColumn;
use Dtc\GridBundle\Grid\Source\ColumnSource;
use Dtc\GridBundle\Tests\Fixtures\AnnotatedGridEntity;
use PHPUnit\Framework\TestCase;

/**
 * Drives the real Doctrine annotation reader path (getColumnSourceInfo ->
 * readAndCacheGridAnnotations -> buildColumnInfoFromGrid) against a fixture
 * entity. This is the bundle's original public API: it must keep working
 * even though the annotation classes gained constructors and attribute
 * markers, on every supported doctrine/annotations version.
 */
class AnnotationColumnSourceTest extends TestCase
{
    /** @var string */
    private $cacheDir;

    protected function setUp(): void
    {
        // doctrine/annotations 1.x only autoloads annotation classes through
        // a registered loader; 2.x removed the registry and always autoloads.
        if (class_exists(AnnotationRegistry::class) && method_exists(AnnotationRegistry::class, 'registerLoader')) {
            AnnotationRegistry::registerLoader('class_exists');
        }
        $this->cacheDir = sys_get_temp_dir().'/dtc_grid_annot_'.bin2hex(random_bytes(4));
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

    public function testReadsColumnsFromAnnotations()
    {
        $info = $this->buildColumnSourceInfo();

        $columns = $info->columns;
        self::assertArrayHasKey('name', $columns);
        self::assertArrayHasKey('email', $columns);
        self::assertInstanceOf(GridColumn::class, $columns['name']);
        self::assertSame('Full Name', $columns['name']->getLabel());
        self::assertSame('Email Address', $columns['email']->getLabel());
    }

    public function testActionColumnResolvedFromNestedAnnotations()
    {
        $info = $this->buildColumnSourceInfo();

        $actionColumns = array_filter($info->columns, function ($column) {
            return $column instanceof ActionGridColumn;
        });
        self::assertCount(1, $actionColumns, 'Expected a single action column from nested @ShowAction + @DeleteAction');
    }

    public function testSortResolvedFromNestedAnnotation()
    {
        $info = $this->buildColumnSourceInfo();

        self::assertSame(['name' => 'ASC'], $info->sort);
    }

    public function testDebugModeResolvesAnnotations()
    {
        $info = $this->buildColumnSourceInfo(true);

        self::assertArrayHasKey('name', $info->columns);
        self::assertSame('Full Name', $info->columns['name']->getLabel());
        self::assertSame(['name' => 'ASC'], $info->sort);
    }

    private function buildColumnSourceInfo($debug = false)
    {
        $reflectionClass = new \ReflectionClass(AnnotatedGridEntity::class);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('getReflectionClass')->willReturn($reflectionClass);
        $metadata->method('getIdentifier')->willReturn(['id']);

        $factory = $this->createMock(ClassMetadataFactory::class);
        $factory->method('getMetadataFor')->willReturn($metadata);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('getMetadataFactory')->willReturn($factory);

        $columnSource = new ColumnSource($this->cacheDir, $debug);

        return $columnSource->getColumnSourceInfo($objectManager, AnnotatedGridEntity::class, true, new AnnotationReader());
    }
}
