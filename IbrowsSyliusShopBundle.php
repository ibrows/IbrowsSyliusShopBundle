<?php

namespace Ibrows\SyliusShopBundle;

use Ibrows\SyliusShopBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class IbrowsSyliusShopBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {


        $interfaces = array(
                'Sylius\Bundle\CartBundle\Model\CartInterface' => 'sylius.model.cart.class',
        );
        $container->addCompilerPass(new CompilerPass($interfaces));
    }
}