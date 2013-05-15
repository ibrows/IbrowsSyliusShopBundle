<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Strategy;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

interface CartDeliveryOptionStrategyInterface extends CartStrategyInterface
{
    /**
     * @param CartInterface $cart
     * @return bool
     */
    public function isPossible(CartInterface $cart);
}