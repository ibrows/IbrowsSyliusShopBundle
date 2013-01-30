<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Sylius\Bundle\CartBundle\Model\CartItemInterface;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Bundle\CartBundle\Entity\CartItem as BaseCartItem;

/**
 * @ORM\Entity
 * @ORM\Table(name="cartitem")
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
     * @var Object
     */
    protected $product;

    public function getProduct()
    {
        return $this->product;
    }

    public function setProduct($product)
    {
        $this->product = $product;
    }

    public function equals(CartItemInterface $item)
    {
        return $this->product === $item->getProduct();
    }
}