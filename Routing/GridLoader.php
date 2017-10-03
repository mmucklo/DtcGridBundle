<?php

namespace Dtc\GridBundle\Routing;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Dtc\GridBundle\Annotation\Grid;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

class GridLoader extends Loader {
    protected $loaded = false;
    protected $registry;
    protected $reader;

    public function __construct(AbstractManagerRegistry $registry, Reader $reader) {
        $this->registry = $registry;
        $this->reader = $reader;
    }

    public function load($resource, $type = null) {
        if ($this->loaded)
        {
            throw new \RuntimeException("GridLoader loaded twice");
        }

        $routes = new RouteCollection();

        $path = '/dtc_grid/'; // @Todo get this prefix out of routing.yml?


        $managers = $this->registry->getManagers();

        if (!$managers) {
            return;
        }

        /** @var ObjectManager $manager */
        foreach ($managers as $manager) {
            $metadatas = $manager->getMetadataFactory()->getAllMetadata();
            if (!$metadatas) {
                continue;
            }
            foreach ($metadatas as $metadata) {
                $reflectionClass = $metadata->getReflectionClass();
                /** @var Grid $gridAnnotation */
                if ($gridAnnotation = $this->reader->getClassAnnotation($reflectionClass, 'Dtc\GridBundle\Annotation\Grid')) {
                    $path =
                    $gridAnnotation->
                }
            }
        }
    }



}