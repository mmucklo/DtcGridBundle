<?php

namespace Dtc\GridBundle\Tests\Manager;

use Dtc\GridBundle\Grid\Source\ColumnSource;
use Dtc\GridBundle\Manager\GridSourceManager;
use Dtc\GridBundle\Tests\Grid\Source\TestGridSource;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

class GridSourceManagerTest extends TestCase
{
    public function testConstruct()
    {
        $container = new Container();
        $container->setParameter('dtc_grid.custom_managers', []);
        $gridSourceManager = new GridSourceManager(new ColumnSource('/tmp', true));
        self::assertNotNull($gridSourceManager->all());
        self::assertIsArray($gridSourceManager->all());
        self::assertEmpty($gridSourceManager->all());
    }

    public function testAdd()
    {
        $container = new Container();
        $container->setParameter('dtc_grid.custom_managers', []);
        $gridSourceManager = new GridSourceManager(new ColumnSource('/tmp', true));
        $gridSource = new TestGridSource();
        $gridSourceManager->add('test_grid_source', $gridSource);
        self::assertSame($gridSource, $gridSourceManager->get('test_grid_source'));
        try {
            $gridSourceManager->get('test_grirce');
            $this->fail('should not get a gridsource here');
        } catch (\Exception $exception) {
        }
    }

    public function testGetNegativeCase()
    {
        $container = new Container();
        $container->setParameter('dtc_grid.custom_managers', []);
        $gridSourceManager = new GridSourceManager(new ColumnSource('/tmp', true));
        $gridSource = new TestGridSource();
        $caught = 0;
        try {
            $gridSourceManager->get('test_grirce');
            $this->fail('should not get a gridsource here');
        } catch (\Exception $exception) {
            ++$caught;
        }
        $gridSourceManager->add('test_grid_source', $gridSource);
        try {
            $gridSourceManager->get('test_grirce');
            $this->fail('should not get a gridsource here');
        } catch (\Exception $exception) {
            ++$caught;
        }
        try {
            $gridSourceManager->get('');
            $this->fail('should not get a gridsource here');
        } catch (\Exception $exception) {
            ++$caught;
        }
        try {
            $gridSourceManager->get(null);
            $this->fail('should not get a gridsource here');
        } catch (\Exception $exception) {
            ++$caught;
        }
        self::assertSame(4, $caught);
    }
}
