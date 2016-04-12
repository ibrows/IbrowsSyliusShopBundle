<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Surcharge;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\AbstractCartStrategy;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

class QuantitySurchargeCartStrategy extends AbstractCartStrategy
{
    /**
     * @var float
     */
    protected $minPrice;

    /**
     * @var float
     */
    protected $surchargePrice;

    /**
     * @param float $minPrice
     * @param float $surchargePrice
     */
    public function __construct($minPrice, $surchargePrice)
    {
        $this->minPrice = $minPrice;
        $this->surchargePrice = $surchargePrice;
    }

    /**
     * @return float
     */
    public function getSurchargePrice()
    {
        return $this->surchargePrice;
    }

    /**
     * @param float $surchargePrice
     *
     * @return QuantitySurchargeCartStrategy
     */
    public function setSurchargePrice($surchargePrice)
    {
        $this->surchargePrice = $surchargePrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getMinPrice()
    {
        return $this->minPrice;
    }

    /**
     * @param float $minPrice
     *
     * @return QuantitySurchargeCartStrategy
     */
    public function setMinPrice($minPrice)
    {
        $this->minPrice = $minPrice;

        return $this;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     *
     * @return bool
     */
    public function accept(CartInterface $cart, CartManager $cartManager)
    {
        return $cart->getTotal() < $this->minPrice;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     *
     * @return AdditionalCartItemInterface[]
     */
    public function compute(CartInterface $cart, CartManager $cartManager)
    {
        return array($this->createAdditionalCartItem($this->surchargePrice));
    }
}
