<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

abstract class AbstractFlatrateDeliveryCartStrategy extends AbstractDeliveryCartStrategy
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
     * @return AdditionalCartItemInterface[]
     */
    public function compute(CartInterface $cart, CartManager $cartManager)
    {
        $steps = $this->steps;
        if(!$steps){
            return array();
        }

        $costs = $this->getCosts($this->steps, $cart->getTotal());

        $item = $this->createAdditionalCartItem();
        $item->setPrice($costs);
        $item->setText($this->getItemText($costs, $cart, $cartManager, $item));

        return array($item);
    }

    /**
     * @param array $steps
     * @param float $total
     * @return float
     */
    protected function getCosts(array $steps, $total)
    {
        $costs = 0.0;
        $oldStepCosts = 0.0;

        foreach($steps as $minTotal => $stepCosts){
            if($total < $minTotal){
                $costs = $oldStepCosts;
                break;
            }
            $oldStepCosts = $stepCosts;
        }

        return $costs;
    }

    /**
     * @param float $costs
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @param AdditionalCartItemInterface $item
     * @return string
     */
    abstract protected function getItemText($costs, CartInterface $cart, CartManager $cartManager, AdditionalCartItemInterface $item);
}