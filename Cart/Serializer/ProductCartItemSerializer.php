<?php

namespace Ibrows\SyliusShopBundle\Cart\Serializer;

use Ibrows\SyliusShopBundle\Model\Cart\Serializer\CartItemSerializerInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;

class ProductCartItemSerializer implements CartItemSerializerInterface
{
    /**
     * @param CartItemInterface $item
     *
     * @return bool
     */
    public function accept(CartItemInterface $item)
    {
        return (bool) $item->getProduct();
    }

    /**
     * @param CartItemInterface $item
     */
    public function serialize(CartItemInterface $item)
    {
        // Force not Proxy
        $product = $item->getProduct();
        $product->getName();

        $item->setProductObj($product);
    }
}
