<?php

namespace Dtc\GridBundle\Tests\Grid\Source;

use Doctrine\Common\Annotations\AnnotationReader;
use Dtc\GridBundle\Tests\Fixtures\ColumnlessGridEntity;

/**
 * A Grid marker with no Column definitions anywhere and reflection
 * disallowed must fail with a clear configuration error before anything is
 * cached — not the historical "Bad column cache" poisoned-cache loop. The
 * fixture carries both the attribute and annotation forms, so the attribute
 * path is exercised on PHP 8 and the annotation path on PHP 7.
 */
class ColumnlessGridTest extends ColumnSourceTestCase
{
    public function testGridWithoutColumnsThrowsClearConfigurationError()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('no Column definitions');
        $this->buildColumnSourceInfo(ColumnlessGridEntity::class, false, new AnnotationReader(), false);
    }
}
