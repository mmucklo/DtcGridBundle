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
                ->arrayNode('custom_managers')
                   ->useAttributeAsKey('name')
                   ->prototype('scalar')->end()
                ->end()
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
                ->arrayNode('bootstrap')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('css')->defaultValue('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css')->end()
                        ->scalarNode('js')->defaultValue('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
