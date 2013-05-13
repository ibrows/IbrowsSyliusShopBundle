<?php

namespace Ibrows\SyliusShopBundle\Cart;

use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;

interface CartItemSerializerInterface
{
    /**
     * @param CartItemInterface $item
     * @return bool
     */
    public function accept(CartItemInterface $item);

    /**
     * @param CartItemInterface $item
     * @return void
     */
    public function serialize(CartItemInterface $item);
}