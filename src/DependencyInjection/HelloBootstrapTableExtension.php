<?php


namespace HelloSebastian\HelloBootstrapTableBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class HelloBootstrapTableExtension extends Extension
{
    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);

        $config = $this->processConfiguration($configuration, $configs);

        if (empty($config['table_options']['bulkActions'])) {
            unset($config['table_options']['bulkActions']);
        }

        $definition = $container->getDefinition('hello_sebastian_hello_bootstrap_table.hello_bootstrap_table_factory');
        $definition->setArgument(4, $config);
    }

}