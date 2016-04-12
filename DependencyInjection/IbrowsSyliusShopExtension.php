<?php

namespace Ibrows\SyliusShopBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class IbrowsSyliusShopExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('repository.xml');
        $loader->load('validator_constraints.xml');

        if (class_exists('Remdan\EasysysConnectorBundle\EasysysConnectorManager')) {
            $loader->load('easysysconnector_services.xml');
        }
    }
}
