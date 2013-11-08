<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_cart_additional_item")
 * @ORM\InheritanceType("JOINED")
 */
class AdditionalCartItem implements AdditionalCartItemInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var CartInterface
     * @ORM\ManyToOne(targetEntity="Ibrows\SyliusShopBundle\Model\CartInterface", inversedBy="additionalItems")
     */
    protected $cart;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=30, precision=65)
     */
    protected $price;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=30, precision=65, name="tax_rate")
     */
    protected $taxRate = 0.0;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=30, precision=65, name="tax_price")
     */
    protected $taxPrice = 0.0;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=30, precision=65, name="price_with_tax")
     */
    protected $priceWithTax = 0.0;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $text;

    /**
     * @var string
     * @ORM\Column(type="string", name="strategy_identifier")
     */
    protected $strategyIdentifier;

    /**
     * @var array
     * @ORM\Column(type="json_array", name="strategy_data")
     */
    protected $strategyData = array();

    /**
     * @var boolean
     * @ORM\Column(type="boolean", name="tax_inclusive")
     */
    protected $taxInclusive = false;

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getText();
    }

    public function calculateTotal()
    {
        $price = $this->getPrice();
        $tax = $this->getTaxFactor();

        if($this->isTaxInclusive() ||  $price == 0 ){
            $this->price =  ( $this->priceWithTax / ($tax + 1) );
            $taxPrice = $this->priceWithTax - $this->price;
            $this->setTaxPrice($taxPrice);
        }else {
            $taxPrice = $price*$tax;
            $this->setTaxPrice($taxPrice);
            $this->setPriceWithTax($price+$taxPrice);
        }
    }

    /**
     * @return string
     */
    public function getPriceRounded()
    {
        return sprintf('%.2f', round($this->getPrice(), 2));
    }

    /**
     * @return string
     */
    public function getTaxPriceRounded()
    {
        return sprintf('%.2f', round($this->getTaxPrice(), 2));
    }

    /**
     * @return string
     */
    public function getTaxRateRounded()
    {
        return sprintf('%.2f', round($this->getTaxRate(), 2));
    }

    /**
     * @return string
     */
    public function getPriceWithTaxRounded()
    {
        return sprintf('%.2f', round($this->getPriceWithTax(), 2));
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return CartInterface
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param CartInterface $cart
     * @return CartInterface
     */
    public function setCart(CartInterface $cart = null)
    {
        $this->cart = $cart;
        return $this;
    }

    /**
     * @return string
     */
    public function getStrategyIdentifier()
    {
        return $this->strategyIdentifier;
    }

    /**
     * @param string $identifier
     * @return AdditionalCartItem
     */
    public function setStrategyIdentifier($identifier)
    {
        $this->strategyIdentifier = $identifier;
        return $this;
    }

    /**
     * @return array
     */
    public function getStrategyData()
    {
        return $this->strategyData;
    }

    /**
     * @param array $data
     * @return AdditionalCartItem
     */
    public function setStrategyData(array $data = array())
    {
        $this->strategyData = $data;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return AdditionalCartItem
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return AdditionalCartItem
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return float
     */
    public function getTaxPrice()
    {
        return $this->taxPrice;
    }

    /**
     * @param float $taxPrice
     * @return AdditionalCartItem
     */
    public function setTaxPrice($taxPrice)
    {
        $this->taxPrice = $taxPrice;
        return $this;
    }

    /**
     * @return float
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * eg. 0.08
     * @return number
     */
    public function getTaxFactor(){
        return $this->taxRate / 100;
    }

    /**
     * @param float $taxRate
     * @return AdditionalCartItem
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;
        return $this;
    }

    /**
     * @return float
     */
    public function getPriceWithTax()
    {
        return $this->priceWithTax;
    }

    /**
     * @param float $priceWithTax
     * @return AdditionalCartItem
     */
    public function setPriceWithTax($priceWithTax)
    {
        $this->priceWithTax = $priceWithTax;
        return $this;
    }


    /**
     * @return boolean
     */
    public function isTaxInclusive()
    {
        return $this->taxInclusive;
    }

    /**
     * @param boolean $mwstinclusive
     * @return \Ibrows\SyliusShopBundle\Entity\CartItem
     */
    public function setTaxInclusive($taxInclusive)
    {
        $this->taxInclusive = $taxInclusive;
        return $this;
    }

}
