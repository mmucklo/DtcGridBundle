<?php

namespace Dtc\GridBundle\DependencyInjection\Compiler;

use Dtc\GridBundle\Util\ColumnUtil;
use Symfony\Component\Config\Resource\DirectoryResource;
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
        if ($container->hasParameter('kernel.project_dir')) {
            $directory = $container->getParameter('kernel.project_dir') . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'dtc_grid';
            if (is_dir($directory)) {
                $finder = new Finder();
                $finder->files()->in(str_replace(\DIRECTORY_SEPARATOR, '/', $directory));
                self::cacheAllFiles($cacheDir, $finder);
            }

            $container->addResource(new DirectoryResource($directory));
        } elseif ($container->hasParameter('kernel.root_dir')) {
            // Typically Symfony versions < 4 using the older directory layout.
            $directory = str_replace(\DIRECTORY_SEPARATOR, '/', $container->getParameter('kernel.root_dir')).'/../src';
            $finder = new Finder();
            $finder->files()->in($directory)->name('dtc_grid.yaml')->name('dtc_grid.yml')->path('Resources/config');
            self::cacheAllFiles($cacheDir, $finder);
            if (class_exists('Symfony\Component\Config\Resource\GlobResource')) {
                $container->addResource(new \Symfony\Component\Config\Resource\GlobResource(str_replace('/', \DIRECTORY_SEPARATOR, $directory),str_replace('/', \DIRECTORY_SEPARATOR, '/**/Resources/config/dtc_grid.yaml'), false));
                $container->addResource(new \Symfony\Component\Config\Resource\GlobResource(str_replace('/', \DIRECTORY_SEPARATOR, $directory),str_replace('/', \DIRECTORY_SEPARATOR, '/**/Resources/config/dtc_grid.yml'), false));
            }
            // TODO: To cover symfony versions that don't support GlobResource, such as 2.x, it would probably be necessary to add a recursive set of FileExistenceResources here.
        }
    }

    /**
     * @param $cacheDir
     * @param Finder $finder
     * @throws \Exception
     */
    private static function cacheAllFiles($cacheDir, Finder $finder) {
        foreach ($finder as $file) {
            ColumnUtil::cacheClassesFromFile($cacheDir, $file->getRealPath());
        }
    }
}
