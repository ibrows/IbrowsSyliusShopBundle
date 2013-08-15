<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Strategy;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\DefaultOptionCartStrategyInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

interface CartDeliveryOptionStrategyInterface extends CartStrategyInterface, CartFormStrategyInterface
{
    /**
     * @return mixed
     */
    public function getDeliveryConditions();

    /**
     * @param mixed $conditions
     * @return CartDeliveryOptionStrategyInterface
     */
    public function setDeliveryConditions($conditions);
}