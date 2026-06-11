<?php

namespace Dtc\GridBundle\Tests\Util;

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
        $this->removeDir($this->tmpDir);
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
        self::assertSame(['columns' => [], 'sort' => []], $result);
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
        ColumnUtil::populateCacheFile($filename, $info);
        $result = include $filename;
        self::assertIsArray($result);
        self::assertArrayHasKey('columns', $result);
        self::assertArrayHasKey('sort', $result);
        self::assertSame(['title' => 'ASC'], $result['sort']);
        self::assertInstanceOf(\Dtc\GridBundle\Grid\Column\GridColumn::class, $result['columns']['title']);
    }

    public function testExtractClassesFromUnreadableFileThrows()
    {
        $this->expectException(\Exception::class);
        ColumnUtil::extractClassesFromFile($this->tmpDir.'/does-not-exist.yaml');
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }
        rmdir($dir);
    }
}
