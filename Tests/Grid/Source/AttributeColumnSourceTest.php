<?php

namespace Dtc\GridBundle\Tests\Grid\Source;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\ObjectManager;
use Dtc\GridBundle\Grid\Column\ActionGridColumn;
use Dtc\GridBundle\Grid\Column\GridColumn;
use Dtc\GridBundle\Grid\Source\ColumnSource;
use Dtc\GridBundle\Tests\Fixtures\AttributeGridEntity;
use PHPUnit\Framework\TestCase;

/**
 * Drives the real attribute-reading path (getColumnSourceInfo ->
 * readAndCacheGridAttributes -> buildColumnInfoFromGrid) against a fixture
 * entity, asserting columns, actions and sort all resolve from attributes.
 *
 * @requires PHP 8.0
 */
class AttributeColumnSourceTest extends TestCase
{
    /** @var string */
    private $cacheDir;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir().'/dtc_grid_attr_'.bin2hex(random_bytes(4));
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

    public function testReadsColumnsActionsAndSortFromAttributes()
    {
        $info = $this->buildColumnSourceInfo();

        $columns = $info->columns;
        self::assertArrayHasKey('name', $columns);
        self::assertArrayHasKey('email', $columns);
        self::assertInstanceOf(GridColumn::class, $columns['name']);
        self::assertSame('Full Name', $columns['name']->getLabel());
        self::assertSame('Email Address', $columns['email']->getLabel());
    }

    public function testActionColumnResolvedFromClassAttributes()
    {
        $info = $this->buildColumnSourceInfo();

        $actionColumns = array_filter($info->columns, function ($column) {
            return $column instanceof ActionGridColumn;
        });
        self::assertCount(1, $actionColumns, 'Expected a single action column from #[ShowAction] + #[DeleteAction]');
    }

    public function testSortResolvedFromClassAttribute()
    {
        $info = $this->buildColumnSourceInfo();

        self::assertSame(['name' => 'ASC'], $info->sort);
    }

    public function testDebugModeResolvesAttributesWithoutReader()
    {
        // In debug mode with no annotation reader, the cache timestamp check
        // must still round-trip the freshly written attribute cache.
        $info = $this->buildColumnSourceInfo(true);

        self::assertArrayHasKey('name', $info->columns);
        self::assertSame('Full Name', $info->columns['name']->getLabel());
        self::assertSame(['name' => 'ASC'], $info->sort);
    }

    private function buildColumnSourceInfo($debug = false)
    {
        $reflectionClass = new \ReflectionClass(AttributeGridEntity::class);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('getReflectionClass')->willReturn($reflectionClass);
        $metadata->method('getIdentifier')->willReturn(['id']);

        $factory = $this->createMock(ClassMetadataFactory::class);
        $factory->method('getMetadataFor')->willReturn($metadata);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('getMetadataFactory')->willReturn($factory);

        $columnSource = new ColumnSource($this->cacheDir, $debug);

        return $columnSource->getColumnSourceInfo($objectManager, AttributeGridEntity::class, true);
    }
}
