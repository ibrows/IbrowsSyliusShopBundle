<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Strategy;

use Sylius\Bundle\CartBundle\Model\CartInterface;

interface CartDeliveryOptionStrategyInterface extends CartDeliveryOptionStrategyInterface
{
    /**
     * @param CartInterface $cart
     * @return bool
     */
    public function isPossible(CartInterface $cart);
}