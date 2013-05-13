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
     * @return string
     */
    public function __toString(){
        return (string)$this->product;
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
}