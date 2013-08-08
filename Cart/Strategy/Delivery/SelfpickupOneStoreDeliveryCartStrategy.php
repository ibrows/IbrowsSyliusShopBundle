<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Delivery;

use Ibrows\SyliusShopBundle\Cart\Strategy\Delivery\SelfpickupDeliveryCartStrategy;
use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

class SelfpickupOneStoreDeliveryCartStrategy extends SelfpickupDeliveryCartStrategy
{
    /**
     * @var array
     */
    protected $oneStore;

    /**
     * @param string $oneStore
     */
    public function __construct($oneStore = null)
    {
        $this->oneStore = $oneStore;
    }

    /**
     * @return string
     */
    public function getOneStore()
    {
        return $this->oneStore;
    }

    /**
     * @param string $oneStore
     * @return $this
     */
    public function setOneStore($oneStore = null)
    {
        $this->oneStore = $oneStore;

        return $this;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return AdditionalCartItemInterface[]
     */
    public function compute(CartInterface $cart, CartManager $cartManager)
    {
        $cart->setDeliveryOptionStrategyServiceData(
            array(
                'store' => $this->getOneStore($cart)
            )
        );

        return array();
    }

}