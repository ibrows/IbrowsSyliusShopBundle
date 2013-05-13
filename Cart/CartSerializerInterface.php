<?php

namespace Ibrows\SyliusShopBundle\Cart;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

interface CartSerializerInterface
{
    /**
     * @param CartInterface $cart
     * @return bool
     */
    public function accept(CartInterface $cart);

    /**
     * @param CartInterface $cart
     * @return void
     */
    public function serialize(CartInterface $cart);
}