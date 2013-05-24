<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Strategy;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Context;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\RedirectResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\PaymentFinishedResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\ErrorRedirectResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\SelfRedirectResponse;

interface CartPaymentOptionStrategyInterface extends CartStrategyInterface, CartFormStrategyInterface
{
    /**
     * @param Context $context
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return PaymentFinishedResponse|ErrorRedirectResponse|SelfRedirectResponse
     */
    public function pay(Context $context, CartInterface $cart, CartManager $cartManager);

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