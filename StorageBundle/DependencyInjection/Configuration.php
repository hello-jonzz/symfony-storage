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
        $root->useAttributeAsKey('storage_name')
            ->prototype('array')
                ->children()
                    ->scalarNode('type')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('bucket')->end()
                    ->scalarNode('bucket_url')->end()
                    ->scalarNode('region')->end()
                    ->scalarNode('endpoint')->end()
                    ->arrayNode('credentials')
                        ->children()
                            ->scalarNode('key')->end()
                            ->scalarNode('secret')->end()
                        ->end()
                    ->end()
                    ->scalarNode('version')->end()
                    ->scalarNode('path')->end()
                ->end()
            ->end()
        ->end();
        return ($treeBuilder);
    }
}
