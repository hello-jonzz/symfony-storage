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
                    ->scalarNode('bucket')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('region')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('endpoint')->isRequired()->cannotBeEmpty()->end()
                    ->arrayNode('credentials')
                        ->children()
                            ->scalarNode('key')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('secret')->isRequired()->cannotBeEmpty()->end()
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
