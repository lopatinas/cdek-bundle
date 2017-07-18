<?php

namespace Lopatinas\CdekBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $tb = new TreeBuilder();
        $root = $tb->root('cdek');
        $this->addGeneralSection($root);

        return $tb;
    }

    protected function addGeneralSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
            ->scalarNode('account')
            ->isRequired()
            ->end()
            ->scalarNode('password')
            ->isRequired()
            ->end()
        ;
    }
}
