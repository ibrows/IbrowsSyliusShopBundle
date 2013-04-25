<?php
namespace Ibrows\SyliusShopBundle\DependencyInjection;
use Symfony\Component\DependencyInjection\Reference;

use Sylius\Bundle\ResourceBundle\DependencyInjection\DoctrineTargetEntitiesResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CompilerPass implements CompilerPassInterface
{
    private $interfaces;

    public function __construct(array $interfaces)
    {
        $this->interfaces = $interfaces;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {

        if (!$container->hasDefinition('doctrine.orm.listeners.resolve_target_entity')) {
            throw new \RuntimeException('Cannot find Doctrine RTE');
        }

        $resolveTargetEntityListener = $container->findDefinition('doctrine.orm.listeners.resolve_target_entity');

        foreach ($this->interfaces as $interface => $parameter) {
            $resolveTargetEntityListener->addMethodCall('addResolveTargetEntity', array($interface, $container->getParameter($parameter), array()));
        }

        if (!$resolveTargetEntityListener->hasTag('doctrine.event_listener')) {
            $resolveTargetEntityListener->addTag('doctrine.event_listener', array('event' => 'loadClassMetadata'));
        }

        //$cartdef = $container->getDefinition('ibrows_syliusshop.cart.manager');
        $currentcartdef = $container->getDefinition('ibrows_syliusshop.currentcart.manager');
        $taggedServices = $container->findTaggedServiceIds('ibrows_syliusshop.additionalitemservice');
        foreach ($taggedServices as $id => $attributes) {
            $currentcartdef->addMethodCall('addAdditionalService', array(new Reference($id)));
        }
    }

}
