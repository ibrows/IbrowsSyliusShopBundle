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

        $this->registerContainerParametersRecursive($container->getParameterBag()->get($this->getAlias()), $container);
    }

    /**
     * @param mixed $config
     * @param ContainerBuilder $container
     * @param string $alias
     */
    protected function registerContainerParametersRecursive($config, ContainerBuilder $container, $prefix = null)
    {
        if(!$prefix){
            $prefix = $this->getAlias();
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($config),
            \RecursiveIteratorIterator::SELF_FIRST);

        foreach($iterator as $value){
            $path = array( );
            for($i = 0; $i <= $iterator->getDepth(); $i++){
                $path[] = $iterator->getSubIterator($i)->key();
            }
            $key = $prefix . '.' . implode(".", $path);
            $container->setParameter($key, $value);
        }
    }
}