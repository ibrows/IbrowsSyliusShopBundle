<?php

namespace Ibrows\SyliusShopBundle\Model\Cart;

interface AdditionalCartItemInterface
{

    /**
     * Returns associated cart.
     *
     * @return CartInterface
     */
    public function getCart();

    /**
     * Sets cart.
     *
     * @param CartInterface
     */
    public function setCart(CartInterface $cart = null);

    public function __toString();
    /**
     * @return int
     */
    public function getId();

    /**
     * @return CartInterface
     */
    public function getText();

    /**
     * @param string $text
     */
    public function setText($text);

    /**
     * @return float
     */
    public function getPrice();

    /**
     * @param float $price
     */
    public function setPrice($price);
}