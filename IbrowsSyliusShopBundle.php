<?php

namespace Ibrows\SyliusShopBundle;

use Ibrows\SyliusShopBundle\DependencyInjection\CompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IbrowsSyliusShopBundle extends Bundle
{
    const TRANSLATION_PREFIX = 'ibrows_sylius_shop';

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $interfaces = array(
            'Sylius\Bundle\CartBundle\Model\CartInterface' => 'sylius.model.cart.class',
        );

        $container->addCompilerPass(new CompilerPass($interfaces));
    }

    /**
     * @return array
     */
    public static function getBundles()
    {
        return array(
            new \Payment\Bundle\SaferpayBundle\PaymentSaferpayBundle(),

            new \FOS\UserBundle\FOSUserBundle(),
            new \FOS\RestBundle\FOSRestBundle(),

            new \Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),

            new \Sylius\Bundle\ResourceBundle\SyliusResourceBundle(),
            new \Sylius\Bundle\CartBundle\SyliusCartBundle(),
            new \Sylius\Bundle\InventoryBundle\SyliusInventoryBundle(),

            new \Ibrows\Bundle\WizardAnnotationBundle\IbrowsWizardAnnotationBundle(),

            new \Sonata\BlockBundle\SonataBlockBundle(),
            new \Sonata\jQueryBundle\SonatajQueryBundle(),
            new \Sonata\AdminBundle\SonataAdminBundle(),
            new \Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new \Sonata\IntlBundle\SonataIntlBundle(),

            new \Ibrows\Bundle\SonataAdminAnnotationBundle\IbrowsSonataAdminAnnotationBundle(),
            new \Knp\Bundle\MenuBundle\KnpMenuBundle(),

            new \Ibrows\SyliusShopBundle\IbrowsSyliusShopBundle()
        );
    }
}