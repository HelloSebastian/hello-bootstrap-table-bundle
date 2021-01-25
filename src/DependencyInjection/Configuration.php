<?php


namespace HelloSebastian\HelloBootstrapTableBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('hello_bootstrap_table');
        $rootNode = $treeBuilder->getRootNode();

        return $treeBuilder;
    }
}