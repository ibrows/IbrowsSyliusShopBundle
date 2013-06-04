<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Strategy;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

interface CartCurrencyStrategyInterface
{
    public function acceptAsDefaultCurrency(CartInterface $cart, CartManager $cartManager);
    public function getDefaultCurrency(CartInterface $cart, CartManager $cartManager);

    public function acceptCurrencyChange($fromCurrency, $toCurrency, CartInterface $cart, CartManager $cartManager);
    public function changeCurrency($fromCurrency, $toCurrency, CartInterface $cart, CartManager $cartManager);
}