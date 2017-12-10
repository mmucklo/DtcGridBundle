<?php

namespace Dtc\GridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class GridSourceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->addDoctrine($container);

        if ($container->has('templating.engine.twig')) {
            $container->getDefinition('dtc_grid.renderer.factory')->addMethodCall('setTwigEngine', [new Reference('templating.engine.twig')]);
        }

        // Add each worker to workerManager, make sure each worker has instance to work
        foreach ($container->findTaggedServiceIds('dtc_grid.source') as $id => $attributes) {
            self::addGridSource($container, $id);
        }
    }

    public function addDoctrine(ContainerBuilder $container)
    {
        $sourceManager = $container->getDefinition('dtc_grid.manager.source');

        if ($container->has('doctrine')) {
            $sourceManager->addMethodCall('setRegistry', [new Reference('doctrine')]);
        }

        if ($container->has('doctrine_mongodb')) {
            $sourceManager->addMethodCall('setMongodbRegistry', [new Reference('doctrine_mongodb')]);
        }
    }

    public static function addGridSource(ContainerBuilder $container, $id)
    {
        $sourceManager = $container->getDefinition('dtc_grid.manager.source');
        $gridSourceDefinition = $container->getDefinition($id);
        $class = $gridSourceDefinition->getClass();

        $refClass = new \ReflectionClass($class);
        $interface = 'Dtc\GridBundle\Grid\Source\GridSourceInterface';

        if (!$refClass->implementsInterface($interface)) {
            throw new \InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, $interface));
        }

        $gridSourceDefinition->addMethodCall('setId', array($id));
        $sourceManager->addMethodCall('add', [$id, new Reference($id)]);
    }
}
