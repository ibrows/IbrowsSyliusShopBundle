<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Strategy;

interface CartDeliveryOptionStrategyInterface extends CartStrategyInterface, CartFormStrategyInterface
{
    /**
     * @return mixed
     */
    public function getDeliveryConditions();

    /**
     * @param mixed $conditions
     *
     * @return CartDeliveryOptionStrategyInterface
     */
    public function setDeliveryConditions($conditions);

    /**
     * @return bool
     */
    public function isSkip();
}
