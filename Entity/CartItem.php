<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Sylius\Bundle\InventoryBundle\Model\StockableInterface;

use Sylius\Bundle\CartBundle\Model\CartItemInterface;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Bundle\CartBundle\Entity\CartItem as BaseCartItem;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_cartitem")
 */
class CartItem extends BaseCartItem
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @var StockableInterface
     * @ORM\OneToOne(targetEntity="\Sylius\Bundle\InventoryBundle\Model\StockableInterface")
     */
    protected $product;

    /**
     * @return \Sylius\Bundle\InventoryBundle\Model\StockableInterface
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

    public function equals(CartItemInterface $item)
    {
        return $this->product === $item->getProduct();
    }

    public function __toString(){
        return $this->product->getInventoryName();
    }
}