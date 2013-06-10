<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Tax;
use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\AbstractCartStrategy;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;

class ProductTaxRateCartStrategy extends AbstractCartStrategy
{
    /**
     * @var float
     */
    protected $taxRateMethod;

    /**
     * @param float $taxRate
     */
    public function __construct($taxRateMethod = 'getTaxRate')
    {
        $this->setTaxRateMethod($taxRateMethod);
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
        $totalpermwst = array();
        foreach ($cart->getItems() as $item) {
            $rate = $this->getTaxRateForItem($item, $cart, $cartManager);
            if (array_key_exists("$rate", $totalpermwst)) {
                $totalpermwst["$rate"] += $item->getTotal();
            } else {
                $totalpermwst["$rate"] = $item->getTotal();
            }
            $item->setTaxRate($rate);
        }
        $mixedrate = 0;
        foreach($totalpermwst as $rate => $value){
            $percent =   $value  / $cart->getTotal();
            $mixedrate += $rate * $percent;
        }
        $mixedrate = round($mixedrate, 2,PHP_ROUND_HALF_UP);
        foreach ($cart->getAdditionalItems() as $item) {
            $item->setTaxRate($mixedrate);
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
        $method = $this->taxRateMethod;
        $product = $item->getProduct();
        if (method_exists($product, $method)) {
            return floatval(''.$product->$method());
        }
        throw new \Exception('No Taxrate found:' . $product . '->' . $method);
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

    /**
     * @return float
     */
    public function getTaxRateMethod()
    {
        return $this->taxRateMethod;
    }

    /**
     * @param float $taxRateMethod
     */
    public function setTaxRateMethod($taxRateMethod)
    {
        $this->taxRateMethod = $taxRateMethod;
        return $this;
    }

}
