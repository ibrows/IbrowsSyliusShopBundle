<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Discount;
use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Cart\Strategy\AbstractCartStrategy;

class QuantityDiscountStrategy extends AbstractCartStrategy
{
    /**
     * @var array
     */
    protected $steps = array();
    /**
     * @var string
     */
    protected $quantitymethod = "getTotal";
    /**
     * @var bool
     */
    protected $quantityFromProduct = false;
    /**
     * @var bool
     */
    protected $roundpercent = true;

    /**
     * @param array $steps
     */
    public function __construct(array $steps, $quantitymethod = "getTotal", $quantityFromProduct = false, $roundpercent = true)
    {
        $this->steps = $steps;
        $this->quantitymethod = $quantitymethod;
        $this->quantityFromProduct = $quantityFromProduct;
        $this->roundpercent = $roundpercent;
    }

    public function accept(CartInterface $cart, CartManager $cartManager)
    {
        return true;
    }

    /**
     * @param number $value
     * @return number
     */
    private static function roundfivers($value){
        return round(2 * $value, 1) / 2;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return AdditionalCartItemInterface[]
     */
    public function compute(CartInterface $cart, CartManager $cartManager)
    {

        $steps = $this->steps;
        if (!$steps) {
            return array();
        }
        $total = $this->getQuantity($cart);
        if ($total <= 0) {
            return array();
        }


        $costs = $this->getStepCosts($this->steps, $total, true);
        if ($costs != 0) {
            if (stripos($costs, '%') !== false) {
                $precent = intval($costs);
                $costs = $total * $precent / 100;
                if($this->roundpercent){
                    $costs = self::roundfivers($costs);
                }
            }
            $costs = $costs * -1;
            $item = $this->createAdditionalCartItem($costs);

            $item->setText($this->getItemText($costs, $cart, $cartManager, $item));
            $item->setStrategyData(array('costs' => $costs, 'total' => $total));
            return array(
                    $item
            );
        }

        return array();
    }

    protected function getQuantity(CartInterface $cart)
    {

        $quant = 0;
        $quantitymethod = $this->quantitymethod;
        foreach ($cart->getItems() as $item) {
            if ($this->quantityFromProduct) {
                $quant += ($item->getProduct()->$quantitymethod() * $item->getQuantity());
            } else {
                $quant += $item->$quantitymethod();
            }

        }
        return $quant;
    }

    /**
     * @param float $cost
     * @return string
     */
    protected function getItemText($cost)
    {
        return $this->getServiceId();
    }

    /**
     * @return string
     */
    public function getQuantitymethod()
    {
        return $this->quantitymethod;
    }

    /**
     * @param string $quantitymethod
     * @return \Ibrows\SyliusShopBundle\Cart\Strategy\Discount\QuantityDiscountStrategy
     */
    public function setQuantitymethod($quantitymethod)
    {
        $this->quantitymethod = $quantitymethod;
        return $this;
    }

    /**
     * @return bool
     */
    public function getQuantityFromProduct()
    {
        return $this->quantityFromProduct;
    }

    /**
     * @param bool $quantityFromProduct
     * @return \Ibrows\SyliusShopBundle\Cart\Strategy\Discount\QuantityDiscountStrategy
     */
    public function setQuantityFromProduct($quantityFromProduct)
    {
        $this->quantityFromProduct = $quantityFromProduct;
        return $this;
    }

    /**
     * @return bool
     */
    public function getRoundpercent()
    {
        return $this->roundpercent;
    }

    /**
     * @param  $roundpercent
     */
    public function setRoundpercent($roundpercent)
    {
        $this->roundpercent = $roundpercent;
        return $this;
    }

}
