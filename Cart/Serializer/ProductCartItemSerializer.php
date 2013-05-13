<?php

namespace Ibrows\SyliusShopBundle\Cart\Serializer;

use Ibrows\SyliusShopBundle\Model\Cart\Serializer\CartItemSerializerInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;

class ProductCartItemSerializer implements CartItemSerializerInterface
{
    /**
     * @param CartItemInterface $item
     * @return bool
     */
    public function accept(CartItemInterface $item)
    {
        return (bool)$item->getProduct();
    }

    /**
     * @param CartItemInterface $item
     * @return void
     */
    public function serialize(CartItemInterface $item)
    {
        $item->setProductObj($item->getProduct());
    }
}