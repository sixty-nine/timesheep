<?php

namespace SixtyNine\Timesheep\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class TimesheepConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('timesheep');

        $treeBuilder->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('database_url')->isRequired()->end()
                ->enumNode('box_style')
                    ->values(['compact', 'borderless', 'symfony-style-guide', 'box', 'box-double'])
                    ->defaultValue('box')
                ->end()
                ->scalarNode('date_format')->defaultValue('d-m-Y')->end()
                ->scalarNode('time_format')->defaultValue('H:i')->end()
                ->integerNode('hours_due_per_day')->defaultValue(8)->end()
                ->floatNode('occupation_rate')->defaultValue(1)->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
