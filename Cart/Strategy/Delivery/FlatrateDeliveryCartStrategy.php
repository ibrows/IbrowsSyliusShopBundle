<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Delivery;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

class FlatrateDeliveryCartStrategy extends AbstractDeliveryCartStrategy
{
    /**
     * @var array
     */
    protected $steps = array();

    /**
     * @param array $steps
     */
    public function __construct(array $steps)
    {
        ksort($steps);
        $this->steps = $steps;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return bool
     */
    public function isPossible(CartInterface $cart, CartManager $cartManager)
    {
        return true;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return AdditionalCartItemInterface[]
     */
    public function compute(CartInterface $cart, CartManager $cartManager)
    {
        $steps = $this->steps;
        if(!$steps){
            return array();
        }

        $costs = $this->getStepCosts($this->steps, $cart->getTotal());
        if($costs != 0){
            $item = $this->createAdditionalCartItem();
            $item->setPrice($costs);
            $item->setText($this->getItemText($costs, $cart, $cartManager, $item));
            $item->setStrategyData(array(
                'costs' => $costs
            ));
            return array($item);
        }

        return array();
    }

    /**
     * @param float $costs
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @param AdditionalCartItemInterface $item
     * @return string
     */
    protected function getItemText($costs, CartInterface $cart, CartManager $cartManager, AdditionalCartItemInterface $item)
    {
        return $this->getServiceId();
    }
}