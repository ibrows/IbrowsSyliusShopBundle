<?php

namespace Ibrows\SyliusShopBundle\Form;

use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartFormStrategyInterface;

class PaymentOptionStrategyType extends AbstractCartFormStrategyType
{
    /**
     * @return CartFormStrategyInterface[]
     */
    protected function getStrategies()
    {
        return $this->cartManager->getPossiblePaymentOptionStrategies();
    }
}
