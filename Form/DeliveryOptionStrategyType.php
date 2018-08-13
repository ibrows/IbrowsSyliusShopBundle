<?php

namespace Ibrows\SyliusShopBundle\Form;

use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartFormStrategyInterface;

class DeliveryOptionStrategyType extends AbstractCartFormStrategyType
{
    /**
     * @return CartFormStrategyInterface[]
     */
    protected function getStrategies($options)
    {
        return $options['cartManager']->getPossibleDeliveryOptionStrategies();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ibr_sylius_deliveryoptionstrategy';
    }
}