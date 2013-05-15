<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Strategy;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

interface CartStrategyInterface
{
    /**
     * @param CartInterface $cart
     * @return void
     */
    public function accept(CartInterface $cart);

    /**
     * @param CartInterface $cart
     * @return void
     */
    public function compute(CartInterface $cart);
}