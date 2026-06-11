<?php

namespace Dtc\GridBundle\Tests\Grid\Source;

use Dtc\GridBundle\Tests\Fixtures\PlainEntity;
use Dtc\GridBundle\Util\ColumnUtil;

/**
 * Column caches written at container compile time (dtc_grid YAML grids)
 * have no request-time regeneration path. In debug mode, when the entity
 * file is newer than such a cache and the class carries no Grid marker,
 * the stale cache must still be served — degrading to reflection columns
 * or "no grid source" would silently drop the YAML configuration.
 */
class StaleCacheFallbackTest extends ColumnSourceTestCase
{
    public function testStaleExternalCacheServedWhenNoGridMarkerExists()
    {
        $cacheFilename = ColumnUtil::createCacheFilename($this->cacheDir, PlainEntity::class);
        ColumnUtil::populateCacheFile($cacheFilename, [
            'columns' => [
                'name' => ['class' => '\Dtc\GridBundle\Grid\Column\GridColumn', 'arguments' => ['name', 'Yaml Label', null, ['sortable' => true], true, null]],
            ],
            'sort' => [],
        ]);
        // Make the cache older than the entity source file, as after an
        // entity edit without a container rebuild.
        touch($cacheFilename, time() - 86400);
        clearstatcache();

        $info = $this->buildColumnSourceInfo(PlainEntity::class, true, null, false);

        self::assertNotNull($info);
        self::assertSame('Yaml Label', $info->columns['name']->getLabel());
    }
}
