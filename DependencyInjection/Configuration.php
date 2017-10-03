<?php

namespace Dtc\GridBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('dtc_grid');

        $rootNode
            ->children()
                ->arrayNode('jq_grid')
                    ->children()
                        ->arrayNode('css')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('js')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('purl')
                    ->defaultValue('https://cdnjs.cloudflare.com/ajax/libs/purl/2.3.1/purl.min.js')
                ->end()
                ->arrayNode('jquery')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('url')
                            ->defaultValue('https://code.jquery.com/jquery-3.2.1.min.js')
                        ->end()
                        ->scalarNode('integrity')
                            ->defaultValue('sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=')
                        ->end()
                        ->scalarNode('crossorigin')
                            ->defaultValue('anonymous')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('datatables')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('css')
                            ->prototype('scalar')->end()
                            ->defaultValue(['https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap.min.css'])
                        ->end()
                        ->arrayNode('js')
                            ->prototype('scalar')->end()
                            ->defaultValue(['https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js',
                                            'https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap.min.js', ])
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('theme')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('css')
                            ->prototype('scalar')->end()
                            ->defaultValue(['https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'])
                        ->end()
                        ->arrayNode('js')
                            ->prototype('scalar')->end()
                            ->defaultValue(['https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js'])
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('page_div_style')->defaultValue('margin: 10px')->end()
            ->end();

        return $treeBuilder;
    }
}
