<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Strategy;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

interface CartPaymentStrategyInterface extends CartStrategyInterface
{
    /**
     * @param Request $request
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return CartPaymentStrategyResponseInterface
     */
    public function pay(Request $request, CartInterface $cart, CartManager $cartManager);

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return bool
     */
    public function isPossible(CartInterface $cart, CartManager $cartManager);

    /**
     * @param RouterInterface $router
     * @return CartPaymentStrategyInterface
     */
    public function setRouter(RouterInterface $router);

    /**
     * @param string $sessionKey
     * @return CartPaymentStrategyInterface
     */
    public function setSessionKey($sessionKey);

    /**
     * @return string
     */
    public function getSessionKey();

    /**
     * @return RouterInterface
     */
    public function getRouter();
}