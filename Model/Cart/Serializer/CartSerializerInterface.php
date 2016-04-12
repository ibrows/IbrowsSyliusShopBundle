<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Serializer;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

interface CartSerializerInterface
{
    /**
     * @param CartInterface $cart
     *
     * @return bool
     */
    public function accept(CartInterface $cart);

    /**
     * @param CartInterface $cart
     */
    public function serialize(CartInterface $cart);
}
