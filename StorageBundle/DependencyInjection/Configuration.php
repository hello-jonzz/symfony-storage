<?php

namespace Bluesquare\StorageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('storage');
        $root->children()
            ->arrayNode('storage_name')
                ->children()
                    ->scalarNode('type')->end()
                ->children()
                    ->scalarNode('bucket')->end()
                ->children()
                    ->scalarNode('region')->end()
                ->children()
                    ->scalarNode('endpoint')->end();
        return ($treeBuilder);
    }
}
