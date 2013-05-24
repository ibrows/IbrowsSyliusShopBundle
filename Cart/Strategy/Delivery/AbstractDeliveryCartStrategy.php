<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Delivery;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\AbstractCartFormStrategy;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartDeliveryOptionStrategyInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class AbstractDeliveryCartStrategy extends AbstractCartFormStrategy implements CartDeliveryOptionStrategyInterface
{
    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return bool
     */
    public function accept(CartInterface $cart, CartManager $cartManager)
    {
        return $cart->getDeliveryOptionStrategyServiceId() == $this->getServiceId();
    }

    /**
     * @param CartInterface $cart
     */
    protected function removeStrategy(CartInterface $cart)
    {
        $cart->setDeliveryOptionStrategyServiceId(null);
        $cart->setDeliveryOptionStrategyServiceData(null);
    }
}