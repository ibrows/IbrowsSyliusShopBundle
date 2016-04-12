<?php

namespace Ibrows\SyliusShopBundle\Form;

use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartFormStrategyInterface;

class DeliveryOptionStrategyType extends AbstractCartFormStrategyType
{
    /**
     * @return CartFormStrategyInterface[]
     */
    protected function getStrategies()
    {
        return $this->cartManager->getPossibleDeliveryOptionStrategies();
    }
}
