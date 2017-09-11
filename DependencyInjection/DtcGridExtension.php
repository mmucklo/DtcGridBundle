<?php

namespace Dtc\GridBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class DtcGridExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('grid.yml');

        $configuration = new Configuration();
        $processor = new Processor();

        $config = $processor->processConfiguration($configuration, $configs);
        $this->setCustomManagers($config, $container);
        $this->setJqGrid($config, $container);
        $this->setBootstrap($config, $container);
    }

    public function setCustomManagers(array $config, ContainerBuilder $container)
    {
        if (isset($config['custom_managers'])) {
            $container->setParameter('dtc_grid.custom_managers', $config['custom_managers']);
        }
    }

    public function setJqGrid(array $config, ContainerBuilder $container)
    {
        $css = isset($config['jq_grid']['css']) ? $config['jq_grid']['css'] : [];
        $js = isset($config['jq_grid']['js']) ? $config['jq_grid']['js'] : [];

        $container->setParameter('dtc_grid.jq_grid.stylesheets', $css);
        $container->setParameter('dtc_grid.jq_grid.javascripts', $js);
    }

    public function setBootstrap(array $config, ContainerBuilder $container)
    {
        $css = isset($config['bootstrap']['css']) ? $config['bootstrap']['css'] : '';
        $js = isset($config['bootstrap']['js']) ? $config['bootstrap']['js'] : '';

        $container->setParameter('dtc_grid.jq_grid.bootstrap_css', $css);
        $container->setParameter('dtc_grid.jq_grid.bootstrap_js', $js);
    }

    public function getAlias()
    {
        return 'dtc_grid';
    }
}
