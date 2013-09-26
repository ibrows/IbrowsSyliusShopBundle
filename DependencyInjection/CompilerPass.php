<?php

namespace Ibrows\SyliusShopBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Reference;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CompilerPass implements CompilerPassInterface
{
    /**
     * @var array
     */
    protected $interfaces;

    /**
     * @param array $interfaces
     */
    public function __construct(array $interfaces)
    {
        $this->interfaces = $interfaces;
    }

    /**
     * @param ContainerBuilder $container
     * @throws \RuntimeException
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

        $this->addTaggedCartSerializers($container);
        $this->addTaggedCartStrategies($container);
        $this->addTaggedCartCurrencyStrategies($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function addTaggedCartSerializers(ContainerBuilder $container)
    {
        if(!$container->hasDefinition('ibrows_syliusshop.currentcart.manager')) {
            return;
        }

        $currentCartManagerDefinition = $container->getDefinition('ibrows_syliusshop.currentcart.manager');

        $taggedCartSerializers = $this->findSortedByPriorityTaggedServiceIds($container, 'ibrows_syliusshop.serializer.cart');
        foreach($taggedCartSerializers as $id => $attributes){
            $currentCartManagerDefinition->addMethodCall(
                'addCartSerializer',
                array(new Reference($id))
            );
        }

        $taggedCartItemSerializers = $this->findSortedByPriorityTaggedServiceIds($container, 'ibrows_syliusshop.serializer.cartitem');
        foreach($taggedCartItemSerializers as $id => $attributes){
            $currentCartManagerDefinition->addMethodCall(
                'addCartItemSerializer',
                array(new Reference($id))
            );
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function addTaggedCartStrategies(ContainerBuilder $container)
    {
        if(
            !$container->hasDefinition('ibrows_syliusshop.currentcart.manager')
            OR
            !$container->hasDefinition('ibrows_syliusshop.cart.manager')
        ){
            return;
        }

        $cartManagerDefinition = $container->getDefinition('ibrows_syliusshop.cart.manager');
        $currentCartManagerDefinition = $container->getDefinition('ibrows_syliusshop.currentcart.manager');

        $taggedCartStrategies = $this->findSortedByPriorityTaggedServiceIds($container, 'ibrows_syliusshop.cart.strategy');
        foreach($taggedCartStrategies as $id => $attributes){
            $strategyServiceDefintion = $container->getDefinition($id);
            $strategyServiceDefintion->addMethodCall(
                'setAdditionalCartItemRepo',
                array(new Reference('ibrows_syliusshop.repository.additional_cart_item'))
            );
            $strategyServiceDefintion->addMethodCall(
                'setServiceId',
                array($id)
            );

            $cartManagerDefinition->addMethodCall(
                'addStrategy',
                array(new Reference($id))
            );

            $currentCartManagerDefinition->addMethodCall(
                'addStrategy',
                array(new Reference($id))
            );
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function addTaggedCartCurrencyStrategies(ContainerBuilder $container)
    {
        if(
            !$container->hasDefinition('ibrows_syliusshop.currentcart.manager')
            OR
            !$container->hasDefinition('ibrows_syliusshop.cart.manager')
        ){
            return;
        }

        $cartManagerDefinition = $container->getDefinition('ibrows_syliusshop.cart.manager');
        $currentCartManagerDefinition = $container->getDefinition('ibrows_syliusshop.currentcart.manager');

        $taggedCartStrategies = $this->findSortedByPriorityTaggedServiceIds($container, 'ibrows_syliusshop.cart.currency.strategy');
        foreach($taggedCartStrategies as $id => $attributes){
            $cartManagerDefinition->addMethodCall(
                'addCurrencyStrategy',
                array(new Reference($id))
            );
            $currentCartManagerDefinition->addMethodCall(
                'addCurrencyStrategy',
                array(new Reference($id))
            );
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string $serviceId
     * @return array
     */
    protected function findSortedByPriorityTaggedServiceIds(ContainerBuilder $container, $serviceId)
    {
        $taggedServices = $container->findTaggedServiceIds($serviceId);
        uasort($taggedServices, function($a, $b) {
            $a = isset($a[0]['priority']) ? $a[0]['priority'] : 0;
            $b = isset($b[0]['priority']) ? $b[0]['priority'] : 0;
            return $a > $b ? -1 : 1;
        });
        return $taggedServices;
    }
}
