<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Tax;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\AbstractCartStrategy;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;

class SimpleTaxRateCartStrategy extends AbstractCartStrategy
{
    /**
     * @var float
     */
    protected $taxRate;

    /**
     * @var boolean
     */
    protected $taxincl = false;

    /**
     * @param float $taxFactor
     * @param bool $taxincl
     * @throws \LogicException
     */
    public function __construct($taxFactor = 0.08, $taxincl = false)
    {
        if($taxFactor > 1){
            throw new \LogicException("TaxFactor is over 100%, sure? - Did you mean ". round(($taxFactor/100), 2) ."?");
        }
        $this->setTaxRate($taxFactor * 100);
        $this->taxincl = $taxincl;
    }

    /**
     * @return float
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * @param float $taxRate
     * @return SimpleTaxRateCartStrategy
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;
        return $this;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return bool
     */
    public function accept(CartInterface $cart, CartManager $cartManager)
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
        foreach($cart->getItems() as $item){
            $item->setTaxRate($this->getTaxRateForItem($item, $cart, $cartManager));
            $item->setTaxInclusive($this->taxincl);
        }
        foreach($cart->getAdditionalItems() as $item){
            $item->setTaxRate($this->getTaxRateForAdditionalItem($item, $cart, $cartManager));
            $item->setTaxInclusive($this->taxincl);
        }
        return array();
    }

    /**
     * @param CartItemInterface $item
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return float
     */
    protected function getTaxRateForItem(CartItemInterface $item, CartInterface $cart, CartManager $cartManager)
    {
        return $this->getTaxRate();
    }

    /**
     * @param AdditionalCartItemInterface $item
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return float
     */
    protected function getTaxRateForAdditionalItem(AdditionalCartItemInterface $item, CartInterface $cart, CartManager $cartManager)
    {
        return $this->getTaxRate();
    }

    /**
     * @param float $taxRate
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @param AdditionalCartItemInterface $item
     * @return string
     */
    protected function getItemText($taxRate, CartInterface $cart, CartManager $cartManager, AdditionalCartItemInterface $item)
    {
        return $this->getServiceId();
    }
}