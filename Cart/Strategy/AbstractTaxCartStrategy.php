<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;

abstract class AbstractTaxCartStrategy extends AbstractCartStrategy
{
    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return bool
     */
    public function accept(CartInterface $cart, CartManager $cartManager)
    {
        return true;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return AdditionalCartItemInterface[]
     */
    public function compute(CartInterface $cart, CartManager $cartManager)
    {
        foreach($cart->getItems() as $item){
            $item->setTaxRate($this->getTaxRateForItem($item, $cart, $cartManager));
        }
        return array();
    }

    /**
     * @param CartItemInterface $item
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return float
     */
    abstract protected function getTaxRateForItem(CartItemInterface $item, CartInterface $cart, CartManager $cartManager);
}