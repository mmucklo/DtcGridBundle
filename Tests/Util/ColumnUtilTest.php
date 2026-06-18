<?php

namespace Dtc\GridBundle\Tests\Util;

use Dtc\GridBundle\Tests\TempDir;
use Dtc\GridBundle\Util\ColumnUtil;
use PHPUnit\Framework\TestCase;

class ColumnUtilTest extends TestCase
{
    /** @var string */
    private $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir().'/dtc_grid_'.bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        TempDir::remove($this->tmpDir);
    }

    public function testCreateCacheFilename()
    {
        $filename = ColumnUtil::createCacheFilename($this->tmpDir, 'App\\Entity\\User');
        $expected = $this->tmpDir.'/DtcGridBundle/App/Entity/User.php';
        self::assertSame($expected, $filename);
        self::assertDirectoryExists(dirname($filename));
    }

    public function testCreateCacheFilenameStripsLeadingBackslash()
    {
        $filename = ColumnUtil::createCacheFilename($this->tmpDir, '\\App\\Entity\\User');
        self::assertStringEndsWith('/App/Entity/User.php', $filename);
    }

    public function testPopulateCacheFileNoColumns()
    {
        // Even with no columns the file must return the full structure:
        // ColumnSource treats an include result without a 'columns' key as
        // a corrupt cache (this used to be a "return false" sentinel whose
        // reader was removed in the 3.x column refactor).
        $filename = ColumnUtil::createCacheFilename($this->tmpDir, 'App\\Entity\\Empty');
        ColumnUtil::populateCacheFile($filename, []);
        $result = include $filename;
        self::assertSame([], $result['columns']);
        self::assertSame([], $result['sort']);
        self::assertSame('runtime', $result['source']);
    }

    public function testPopulateCacheFileWithColumns()
    {
        $filename = ColumnUtil::createCacheFilename($this->tmpDir, 'App\\Entity\\Post');
        $info = [
            'columns' => [
                'title' => [
                    'class' => '\\Dtc\\GridBundle\\Grid\\Column\\GridColumn',
                    'arguments' => ['title', 'Title', null, [], true, null],
                ],
            ],
            'sort' => ['title' => 'ASC'],
        ];
        ColumnUtil::populateCacheFile($filename, $info, 'compile');
        $result = include $filename;
        self::assertIsArray($result);
        self::assertArrayHasKey('columns', $result);
        self::assertArrayHasKey('sort', $result);
        self::assertSame(['title' => 'ASC'], $result['sort']);
        self::assertSame('compile', $result['source']);
        self::assertInstanceOf(\Dtc\GridBundle\Grid\Column\GridColumn::class, $result['columns']['title']);
    }

    public function testPopulateCacheFileEscapesKeysAndLabels()
    {
        // Column keys can be user-controlled (YAML column names); a raw
        // apostrophe in a single-quoted key would produce an unparseable file.
        $filename = ColumnUtil::createCacheFilename($this->tmpDir, 'App\\Entity\\Quoted');
        $info = [
            'columns' => [
                "owner's" => [
                    'class' => '\\Dtc\\GridBundle\\Grid\\Column\\GridColumn',
                    'arguments' => ["owner's", "Owner's Label", null, [], true, null],
                ],
            ],
            'sort' => ["owner's" => 'ASC'],
        ];
        ColumnUtil::populateCacheFile($filename, $info);
        $result = include $filename;
        self::assertArrayHasKey("owner's", $result['columns']);
        self::assertSame(["owner's" => 'ASC'], $result['sort']);
        self::assertSame("Owner's Label", $result['columns']["owner's"]->getLabel());
    }

    public function testInstantiateColumnInfoMaterializesObjects()
    {
        $info = [
            'columns' => [
                'title' => [
                    'class' => '\\Dtc\\GridBundle\\Grid\\Column\\GridColumn',
                    'arguments' => ['title', 'Title', null, [], true, null],
                ],
            ],
            'sort' => ['title' => 'ASC'],
        ];
        $materialized = ColumnUtil::instantiateColumnInfo($info);
        self::assertInstanceOf(\Dtc\GridBundle\Grid\Column\GridColumn::class, $materialized['columns']['title']);
        self::assertSame('Title', $materialized['columns']['title']->getLabel());
        self::assertSame(['title' => 'ASC'], $materialized['sort']);
    }

    public function testExtractClassesFromUnreadableFileThrows()
    {
        $this->expectException(\Exception::class);
        ColumnUtil::extractClassesFromFile($this->tmpDir.'/does-not-exist.yaml');
    }
}
