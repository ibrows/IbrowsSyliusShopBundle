<?php

namespace Ibrows\SyliusShopBundle\Entity;
use Ibrows\SyliusShopBundle\Model\Product\ProductInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Sylius\Bundle\CartBundle\Entity\CartItem as BaseCartItem;
use Sylius\Bundle\CartBundle\Model\CartItemInterface as BaseCartItemInterface;

use Doctrine\ORM\Mapping as ORM;

use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_cart_additional_item")
 */
class AdditionalCartItem
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Cart.
     *
     * @var CartInterface
     * @ORM\Column(type="string")
     */
    protected $text;

    /**
     * Cart.
     *
     * @var CartInterface
     * @ORM\ManyToOne(targetEntity="Sylius\Bundle\CartBundle\Model\CartInterface")
     */
    protected $cart;

    /**
     * Total value.
     * @ORM\Column(type="decimal", scale=2, precision=11)
     * @var float
     */
    protected $price;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getText();
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
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param CartInterface $text
     */
    public function setText(CartInterface $text)
    {
        $this->text = $text;
        return $this;
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
     */
    public function setCart(CartInterface $cart)
    {
        $this->cart = $cart;
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
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

}
