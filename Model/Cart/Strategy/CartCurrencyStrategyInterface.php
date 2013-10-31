<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Strategy;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

interface CartCurrencyStrategyInterface
{
    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return CartCurrencyStrategyInterface
     */
    public function acceptAsDefaultCurrency(CartInterface $cart, CartManager $cartManager);

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return string
     */
    public function getDefaultCurrency(CartInterface $cart, CartManager $cartManager);

    /**
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return bool
     */
    public function acceptCurrencyChange($fromCurrency, $toCurrency, CartInterface $cart, CartManager $cartManager);

    /**
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return void
     */
    public function changeCurrency($fromCurrency, $toCurrency, CartInterface $cart, CartManager $cartManager);
}