<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Strategy;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

interface CartStrategyInterface
{
    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return void
     */
    public function accept(CartInterface $cart, CartManager $cartManager);

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return void
     */
    public function compute(CartInterface $cart, CartManager $cartManager);

    /**
     * @return string
     */
    public function getServiceId();

    /**
     * @param string $id
     * @return CartStrategyInterface
     */
    public function setServiceId($id);

    /**
     * @param string $className
     * @return CartStrategyInterface
     */
    public function setAdditionalCartItemClass($className);

    /**
     * @return string
     */
    public function getAdditionalCartItemClass();
}