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
        if($this->getTaxincl())
            $total = $cart->getTotalWithTax();
        else
            $total = $cart->getTotal();
        if($total <= 0){
            return array();
        }
        $costs = $this->getStepCosts($this->steps, $total);
        if($costs != 0){
            $item = $this->createAdditionalCartItem($costs);
            $item->setText($this->getItemText($costs, $cart, $cartManager, $item));
            $item->setStrategyData(array(
                'costs' => $costs,
                'total' => $total
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