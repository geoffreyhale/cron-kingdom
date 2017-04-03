<?php
namespace CronkdBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cronkd');

        $rootNode
            ->children()
                ->arrayNode('teams')
                    ->children()
                        ->scalarNode('max_size')->end()
                    ->end()
                ->end()
                ->arrayNode('resources')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('initial')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}