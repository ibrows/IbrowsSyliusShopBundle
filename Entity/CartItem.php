<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface as BaseCartItemInterface;

use Sylius\Bundle\InventoryBundle\Model\StockableInterface;
use Sylius\Bundle\CartBundle\Model\CartItemInterface;
use Sylius\Bundle\CartBundle\Entity\CartItem as BaseCartItem;

use Doctrine\ORM\Mapping as ORM;

use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_cart_item")
 */
class CartItem extends BaseCartItem implements BaseCartItemInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var StockableInterface
     * @ORM\ManyToOne(targetEntity="Sylius\Bundle\InventoryBundle\Model\StockableInterface")
     */
    protected $product;

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
     * @return StockableInterface
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param StockableInterface $product
     */
    public function setProduct(StockableInterface $product)
    {
        $this->product = $product;
    }

    /**
     * @param CartItemInterface $item
     * @return bool
     */
    public function equals(CartItemInterface $item)
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
     * @return DateTime
     */
    public function getDeliveredAt()
    {
        return $this->delivered;
    }
}