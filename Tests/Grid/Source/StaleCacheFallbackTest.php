<?php

namespace Dtc\GridBundle\Tests\Grid\Source;

use Dtc\GridBundle\Tests\Fixtures\PlainEntity;
use Dtc\GridBundle\Util\ColumnUtil;

/**
 * Cache provenance governs the timestamp-stale fallback. A compile-time cache
 * (dtc_grid YAML grids, written at container build with no request-time
 * regeneration path) is served even when stale; a stale runtime cache is not
 * resurrected, so removing a Grid marker takes effect rather than the deleted
 * config rendering forever.
 */
class StaleCacheFallbackTest extends ColumnSourceTestCase
{
    public function testStaleCompileCacheServedWhenNoGridMarkerExists()
    {
        $cacheFilename = ColumnUtil::createCacheFilename($this->cacheDir, PlainEntity::class);
        ColumnUtil::populateCacheFile($cacheFilename, [
            'columns' => [
                'name' => ['class' => '\Dtc\GridBundle\Grid\Column\GridColumn', 'arguments' => ['name', 'Yaml Label', null, ['sortable' => true], true, null]],
            ],
            'sort' => [],
        ], 'compile');
        // Make the cache older than the entity source file, as after an
        // entity edit without a container rebuild.
        touch($cacheFilename, time() - 86400);
        clearstatcache();

        $info = $this->buildColumnSourceInfo(PlainEntity::class, true, null, false);

        self::assertNotNull($info);
        self::assertSame('Yaml Label', $info->columns['name']->getLabel());
    }

    public function testStaleRuntimeCacheNotResurrected()
    {
        // A runtime cache (written when the class had a Grid marker) must not
        // outlive a since-removed marker: PlainEntity has none, so a stale
        // runtime cache should be ignored and the resolver fall through.
        $cacheFilename = ColumnUtil::createCacheFilename($this->cacheDir, PlainEntity::class);
        ColumnUtil::populateCacheFile($cacheFilename, [
            'columns' => [
                'name' => ['class' => '\Dtc\GridBundle\Grid\Column\GridColumn', 'arguments' => ['name', 'Deleted Marker Label', null, ['sortable' => true], true, null]],
            ],
            'sort' => [],
        ], 'runtime');
        touch($cacheFilename, time() - 86400);
        clearstatcache();

        // Reflection disallowed and no marker: the stale runtime cache is not
        // served, so there is no grid source at all.
        $info = $this->buildColumnSourceInfo(PlainEntity::class, true, null, false);

        self::assertNull($info);
    }

    public function testEmptyColumnCacheIsTreatedAsMiss()
    {
        // A cache file with an empty column set (e.g. a dtc_grid YAML grid
        // declaring `columns: {}`) must not short-circuit into a silent
        // zero-column grid; it is a miss, so with no marker and reflection
        // disallowed there is no grid source.
        $cacheFilename = ColumnUtil::createCacheFilename($this->cacheDir, PlainEntity::class);
        ColumnUtil::populateCacheFile($cacheFilename, ['columns' => [], 'sort' => []], 'compile');

        $info = $this->buildColumnSourceInfo(PlainEntity::class, false, null, false);

        self::assertNull($info);
    }
}
