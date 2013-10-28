<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Ibrows\SyliusShopBundle\Model\Product\ProductInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;
use Sylius\Bundle\CartBundle\Entity\CartItem as BaseCartItem;
use Sylius\Bundle\CartBundle\Model\CartItemInterface as BaseCartItemInterface;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_cart_item")
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(name="unitPrice",
 *          column=@ORM\Column(
 *              name = "unit_price",
 *              type = "decimal",
 *              scale = 30,
 *              precision = 65
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="total",
 *          column=@ORM\Column(
 *              name = "total",
 *              type = "decimal",
 *              scale = 30,
 *              precision = 65
 *          )
 *      )
 * })
 * @ORM\InheritanceType("JOINED")
 */
class CartItem extends BaseCartItem implements CartItemInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var ProductInterface
     * @ORM\ManyToOne(targetEntity="Ibrows\SyliusShopBundle\Model\Product\ProductInterface")
     */
    protected $product;

    /**
     * @var ProductInterface $productObj
     * @ORM\Column(type="object", name="product_obj", nullable=true)
     */
    protected $productObj;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $delivered = null;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=30, precision=65, name="tax_rate")
     */
    protected $taxRate = 0.0;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=30, precision=65, name="tax_price")
     */
    protected $taxPrice = 0;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=30, precision=65, name="total_with_tax_price")
     */
    protected $totalWithTaxPrice = 0;

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
        return (string) $this->product;
    }

    public function calculateTotal()
    {
        $total = $this->quantity * $this->unitPrice;
        $taxfactor = $this->getTaxFactor();

        if($this->isTaxInclusive()){
            $this->totalWithTaxPrice = $total;
            $this->total =  ( $total / ($taxfactor + 1) );
            $taxPrice = $this->totalWithTaxPrice - $this->total;
            $this->setTaxPrice($taxPrice);
        }else{
            $this->total = $total;
            $taxPrice = $total * $taxfactor;
            $this->setTaxPrice($taxPrice);
            $this->totalWithTaxPrice = ($total + $taxPrice);
        }
    }

    /**
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param ProductInterface $product
     * @return CartItem
     */
    public function setProduct(ProductInterface $product = null)
    {
        $this->product = $product;
        $this->setUnitPrice($product->getPrice());
        return $this;
    }

    /**
     * @return ProductInterface
     */
    public function getProductObj()
    {
        return $this->productObj;
    }

    /**
     * @param ProductInterface $product
     * @return CartItem
     */
    public function setProductObj(ProductInterface $product = null)
    {
        $this->productObj = $product;
        return $this;
    }

    /**
     * @param BaseCartItemInterface $item
     * @return bool
     */
    public function equals(BaseCartItemInterface $item)
    {
        return $this->product === $item->getProduct();
    }

    /**
     * @return bool
     */
    public function isDelivered()
    {
        return $this->delivered !== null;
    }

    /**
     * @param bool $flag
     * @return CartItemInterface
     */
    public function setDelivered($flag = true)
    {
        if (false === $flag) {
            $this->delivered = null;
        } else {
            $this->delivered = new DateTime;
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantityNotAvailable()
    {
        return $this->getQuantity() - $this->getProduct()->getOnHand();
    }

    /**
     * @return CartItem
     */
    public function setQuantityToAvailable()
    {
        $this->setQuantity($this->getProduct()->getOnHand());
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDeliveredAt()
    {
        return $this->delivered;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getProduct()->getName();
    }

    /**
     * @return float (eg. 8)
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * @param float $taxRate (eg. 8)
     * @return CartItem
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;
        return $this;
    }

    /**
     * eg. 0.08
     * @return number
     */
    public function getTaxFactor()
    {
        return $this->taxRate / 100;
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
     * @return CartItem
     */
    public function setTaxPrice($taxPrice)
    {
        $this->taxPrice = $taxPrice;
        return $this;
    }

    /**
     * @return float
     */
    public function getTotalWithTaxPrice()
    {
        return $this->totalWithTaxPrice;
    }

    /**
     * @param float $totalWithTaxPrice
     * @return CartItem
     */
    public function setTotalWithTaxPrice($totalWithTaxPrice)
    {
        $this->totalWithTaxPrice = $totalWithTaxPrice;
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
