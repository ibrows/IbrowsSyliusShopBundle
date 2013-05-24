<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Strategy;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

interface CartDeliveryOptionStrategyInterface extends CartStrategyInterface, CartFormStrategyInterface
{
    /**
     * @param bool $flag
     * @return CartDeliveryOptionStrategyInterface
     */
    public function setDefault($flag);

    /**
     * @return bool
     */
    public function isDefault();
}