<?php

namespace Dtc\GridBundle\DependencyInjection\Compiler;

use Dtc\GridBundle\Util\ColumnUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;

class GridSourceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        self::addDoctrine($container);

        if ($container->has('twig')) {
            $container->getDefinition('dtc_grid.renderer.factory')->addMethodCall('setTwigEnvironment', [new Reference('twig')]);
        }

        // Add each worker to workerManager, make sure each worker has instance to work
        foreach ($container->findTaggedServiceIds('dtc_grid.source') as $id => $attributes) {
            self::addGridSource($container, $id);
        }

        self::addGridFiles($container);
    }

    private static function addDoctrine(ContainerBuilder $container)
    {
        $sourceManager = $container->getDefinition('dtc_grid.manager.source');

        if ($container->has('doctrine')) {
            $sourceManager->addMethodCall('setRegistry', [new Reference('doctrine')]);
        }

        if ($container->has('doctrine_mongodb')) {
            $sourceManager->addMethodCall('setMongodbRegistry', [new Reference('doctrine_mongodb')]);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param $id
     *
     * @throws \ReflectionException
     */
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

    /**
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    private static function addGridFiles(ContainerBuilder $container)
    {
        $cacheDir = $container->getParameter('kernel.cache_dir');
        if ($container->hasParameter('kernel.project_root')) {
            $directory = $container->getParameter('kernel.project_root').DIRECTORY_SEPARATOR.'config/dtc_grid';
            self::cacheAllFilesInDirectory($cacheDir, $directory);
        } elseif ($container->hasParameter('kernel.root_dir')) {
            $searchDirectories[] = $container->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR.'/../src/Resources/config';
            $searchDirectories[] = $container->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR.'/../src/*/Resources/config';
            $searchDirectories[] = $container->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR.'/../src/*/*/Resources/config';
            $searchDirectories[] = $container->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR.'/../src/*/*/*/Resources/config';
            foreach ($searchDirectories as $directory) {
                self::cacheAllFilesInDirectory($cacheDir, $directory, function ($filename) {
                    if ('dtc_grid.yaml' === $filename || 'dtc_grid.yml' === $filename) {
                        return true;
                    }

                    return false;
                });
            }
        }
    }

    /**
     * @param $cacheDir
     * @param $directory
     * @param \Closure|null $checkFunc
     *
     * @throws \Exception
     */
    private static function cacheAllFilesInDirectory($cacheDir, $directory, \Closure $checkFunc = null)
    {
        $finder = new Finder();
        $finder->files()->in($directory);
        foreach ($finder as $file) {
            $filename = $file->getFilename();
            if (null !== $checkFunc && !$checkFunc($filename)) {
                continue;
            }
            ColumnUtil::cacheClassesFromFile($cacheDir, $file->getRealPath());
        }
    }
}
