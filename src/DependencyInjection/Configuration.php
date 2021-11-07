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

        $rootNode
            ->children()
                ->append($this->addTableDatasetOptions())
                ->append($this->addTableOptions())
                ->append($this->addActionButtonOptions())
            ->end()
        ->end()
        ;

        return $treeBuilder;
    }

    private function addActionButtonOptions()
    {
        $treeBuilder = new TreeBuilder('action_button_options');
        $node = $treeBuilder->getRootNode();

        $node
            ->addDefaultsIfNotSet()
            ->normalizeKeys(false)
            ->children()
                ->scalarNode('classNames')->end()
            ->end();

        return $node;
    }

    private function addTableOptions()
    {
        $treeBuilder = new TreeBuilder('table_options');
        $node = $treeBuilder->getRootNode();

        $node
            ->addDefaultsIfNotSet()
            ->normalizeKeys(false)
            ->children()
                ->scalarNode('tableClassNames')->end()
                ->booleanNode('enableCheckbox')->end()
                ->scalarNode('bulkIdentifier')->end()
                ->scalarNode('bulkUrl')->end()
                ->scalarNode('bulkActionSelectClassNames')->end()
                ->scalarNode('bulkButtonName')->end()
                ->scalarNode('bulkButtonClassNames')->end()
                ->arrayNode('bulkActions')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')
                ->end()
            ->end();

        return $node;
    }

    private function addTableDatasetOptions()
    {
        $treeBuilder = new TreeBuilder('table_dataset_options');
        $node = $treeBuilder->getRootNode();

        $node
            ->addDefaultsIfNotSet()
            ->normalizeKeys(false)
            ->children()
                ->booleanNode('pagination')->end()
                ->booleanNode('search')->end()
                ->booleanNode('show-columns')->end()
                ->booleanNode('show-columns-toggle-all')->end()
                ->booleanNode('show-footer')->end()
                ->booleanNode('show-refresh')->end()
                ->booleanNode('filter-control')->end()
                ->scalarNode('toolbar')->end()
                ->scalarNode('page-list')->end()
                ->integerNode('page-size')->end()
                ->booleanNode('sort-reset')->end()
                ->scalarNode('pagination-V-Align')->end()
                ->scalarNode('undefined-text')->end()
                ->scalarNode('locale')->end()
                ->booleanNode('advanced-search')->end()
                ->scalarNode('id-table')->end()
                ->scalarNode('icons-prefix')->end()
                ->append($this->addIconsOptions())
                ->booleanNode('click-to-select')->end()
                ->booleanNode('show-jump-to')->end()
                ->booleanNode('show-export')->end()
                ->scalarNode('export-types')->end()
                ->booleanNode('detail-view')->end()
                ->scalarNode('detail-formatter')->end()
                ->scalarNode('detail-view-align')->end()
                ->booleanNode('detail-view-icon')->end()
                ->booleanNode('detail-view-by-click')->end()
                ->booleanNode('sticky-header')->end()
                ->integerNode('sticky-header-offset-left')->end()
                ->integerNode('sticky-header-offset-right')->end()
                ->integerNode('sticky-header-offset-y')->end()
                ->booleanNode('checkbox-header')->end()
                ->booleanNode('escape')->end()
                ->integerNode('height')->end()
                ->booleanNode('multiple-select-row')->end()
                ->scalarNode('sort-name')->end()
                ->scalarNode('sort-order')->end()
            ->end();

        return $node;
    }

    private function addIconsOptions()
    {
        $treeBuilder = new TreeBuilder('icons');
        $node = $treeBuilder->getRootNode();

        $node
            ->addDefaultsIfNotSet()
            ->normalizeKeys(false)
            ->children()
                ->scalarNode('advancedSearchIcon')->end()
                ->scalarNode('paginationSwitchDown')->end()
                ->scalarNode('paginationSwitchUp')->end()
                ->scalarNode('columns')->end()
                ->scalarNode('refresh')->end()
                ->scalarNode('export')->end()
                ->scalarNode('detailOpen')->end()
                ->scalarNode('detailClose')->end()
                ->scalarNode('toggleOff')->end()
                ->scalarNode('toggleOn')->end()
                ->scalarNode('fullscreen')->end()
                ->scalarNode('search')->end()
                ->scalarNode('clearSearch')->end()
            ->end();

        return $node;
    }
}
