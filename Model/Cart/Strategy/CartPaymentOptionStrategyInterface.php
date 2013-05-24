<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Strategy;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

interface CartPaymentOptionStrategyInterface extends CartStrategyInterface, CartFormStrategyInterface
{
    /**
     * @param Request $request
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return mixed
     */
    public function pay(Request $request, CartInterface $cart, CartManager $cartManager);

    /**
     * @return bool
     */
    public function isTestMode();

    /**
     * @param bool $flag
     * @return CartPaymentOptionStrategyInterface
     */
    public function setTestMode($flag);

    /**
     * @param RouterInterface $router
     * @return CartPaymentOptionStrategyInterface
     */
    public function setRouter(RouterInterface $router);

    /**
     * @return RouterInterface
     */
    public function getRouter();
}