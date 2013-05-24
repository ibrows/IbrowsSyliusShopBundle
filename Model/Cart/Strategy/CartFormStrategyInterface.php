<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Strategy;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Cart\CartManager;
use Symfony\Component\Form\FormTypeInterface;

interface CartFormStrategyInterface extends CartStrategyInterface, FormTypeInterface
{
    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return bool
     */
    public function isPossible(CartInterface $cart, CartManager $cartManager);

    /**
     * @return string
     */
    public function __toString();

    /**
     * @param CartInterface $cart
     * @return string
     */
    public function getFullPaymentMethodName(CartInterface $cart);

    /**
     * @return bool
     */
    public function isParentVisible();

    /**
     * @param bool $flag
     * @return CartFormStrategyInterface
     */
    public function setParentVisible($flag);
}