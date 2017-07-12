<?php

namespace Dtc\GridBundle\Tests\Manager;

use Dtc\GridBundle\Manager\GridSourceManager;
use Dtc\GridBundle\Tests\Grid\Source\TestGridSource;
use PHPUnit\Framework\TestCase;

class GridSourceManagerTest extends TestCase {

    public function testConstruct() {
        $gridSourceManager = new GridSourceManager();
        self::assertNotNull($gridSourceManager->all());
        self::assertInternalType('array', $gridSourceManager->all());
        self::assertEmpty($gridSourceManager->all());
    }

    public function testAdd() {
        $gridSourceManager = new GridSourceManager();
        $gridSource = new TestGridSource();
        $gridSourceManager->add('test_grid_source', $gridSource);
        self::assertSame($gridSource, $gridSourceManager->get('test_grid_source'));
        self::assertNull($gridSourceManager->get('test_grirce'));
    }

    public function testGetNegativeCase()
    {
        $gridSourceManager = new GridSourceManager();
        $gridSource = new TestGridSource();
        self::assertNull($gridSourceManager->get('test_grirce'));
        $gridSourceManager->add('test_grid_source', $gridSource);
        self::assertNull($gridSourceManager->get('test_grirce'));
        self::assertNull($gridSourceManager->get(''));
        self::assertNull($gridSourceManager->get(null));
    }
}