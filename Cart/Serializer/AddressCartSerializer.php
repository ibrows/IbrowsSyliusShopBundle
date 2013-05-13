<?php

namespace Ibrows\SyliusShopBundle\Cart\Serializer;

use Ibrows\SyliusShopBundle\Model\Cart\Serializer\CartSerializerInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

class AddressCartSerializer implements CartSerializerInterface
{
    /**
     * @param CartInterface $cart
     * @return bool
     */
    public function accept(CartInterface $cart)
    {
        return $cart->getDeliveryAddress() && $cart->getInvoiceAddress();
    }

    /**
     * @param CartInterface $cart
     */
    public function serialize(CartInterface $cart)
    {
        $cart->setDeliveryAddressObj($cart->getDeliveryAddress());
        $cart->setInvoiceAddressObj($cart->getInvoiceAddress());
    }
}