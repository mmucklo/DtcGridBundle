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
                ->arrayNode('reflection')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->variableNode('allowed_entities')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('jq_grid')
                    ->children()
                        ->variableNode('css')->end()
                        ->variableNode('js')->end()
                        ->variableNode('options')->end()
                        ->arrayNode('local')
                            ->children()
                                ->arrayNode('css')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('js')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('purl')
                    ->defaultValue('//cdnjs.cloudflare.com/ajax/libs/purl/2.3.1/purl.min.js')
                ->end()
                ->arrayNode('jquery')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('url')
                            ->defaultValue('//code.jquery.com/jquery-3.4.1.min.js')
                        ->end()
                        ->scalarNode('integrity')
                            ->defaultValue('sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=')
                        ->end()
                        ->scalarNode('crossorigin')
                            ->defaultValue('anonymous')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('datatables')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->defaultNull()->end()
                        ->variableNode('css')
                            ->defaultValue(['//cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css'])
                        ->end()
                        ->variableNode('js')
                            ->defaultValue(['//cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js',
                                            '//cdn.datatables.net/1.10.20/js/dataTables.bootstrap.min.js', ])
                        ->end()
                        ->arrayNode('local')
                            ->children()
                                ->arrayNode('css')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('js')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->variableNode('options')->end()
                    ->end()
                ->end()
                ->arrayNode('table')
                    ->children()
                        ->variableNode('options')->end()
                    ->end()
                ->end()
                ->arrayNode('theme')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->variableNode('css')
                            ->defaultValue(['https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css'])
                        ->end()
                        ->variableNode('js')
                            ->defaultValue(['https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js'])
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('page_div_style')->defaultValue('margin: 10px')->end()
            ->end();

        return $treeBuilder;
    }
}
