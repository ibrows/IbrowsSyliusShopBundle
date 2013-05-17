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
     * @ORM\Column(type="decimal", scale=2, precision=11, name="tax_rate")
     */
    protected $taxRate = 0.0;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=2, precision=11, name="tax_price")
     */
    protected $taxPrice = 0;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=2, precision=11, name="total_with_tax_price")
     */
    protected $totalWithTaxPrice = 0;

    /**
     * @return string
     */
    public function __toString(){
        return (string)$this->product;
    }

    public function calculateTotal()
    {
        parent::calculateTotal();

        $total = $this->getTotal();
        $taxRate = $this->getTaxRate();
        $taxPrice = $total*$taxRate;

        $this->setTaxPrice($taxPrice);
        $this->setTotalWithTaxPrice($total+$taxPrice);
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
        if(false === $flag){
            $this->delivered = null;
        }else{
            $this->delivered = new DateTime;
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantityNotAvailable(){
        return $this->getQuantity() - $this->getProduct()->getOnHand();
    }

    /**
     * @return CartItem
     */
    public function setQuantityToAvailable(){
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
     * @return float
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * @param float $taxRate
     * @return CartItem
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;
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
}