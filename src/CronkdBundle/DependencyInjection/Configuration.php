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
                ->arrayNode('resource_types')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('resources')
                    ->prototype('array')
                        ->children()
                            ->integerNode('initial')->min(0)->end()
                            ->scalarNode('type')->end()
                            ->scalarNode('description')->end()
                            ->scalarNode('attack')->defaultValue(0)->end()
                            ->scalarNode('defense')->defaultValue(0)->end()
                            ->scalarNode('value')->end()
                            ->scalarNode('capacity')->defaultValue(0)->end()
                            ->booleanNode('can_be_probed')->defaultValue(true)->end()
                            ->arrayNode('action')
                                ->children()
                                    ->scalarNode('verb')->end()
                                    ->scalarNode('description')->end()
                                    ->arrayNode('inputs')
                                        ->prototype('array')
                                            ->children()
                                                ->integerNode('quantity')
                                                    ->min(1)
                                                ->end()
                                                ->booleanNode('requeue')->defaultValue(false)->end()
                                                ->integerNode('queue_size')
                                                    ->defaultValue(0)
                                                    ->min(0)
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('output')
                                        ->children()
                                            ->integerNode('quantity')
                                                ->min(1)
                                            ->end()
                                            ->integerNode('queue_size')
                                                ->defaultValue(0)
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}