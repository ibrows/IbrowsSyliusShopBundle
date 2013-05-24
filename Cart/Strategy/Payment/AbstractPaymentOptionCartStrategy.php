<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Payment;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\AbstractCartFormStrategy;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartPaymentOptionStrategyInterface;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractPaymentOptionCartStrategy extends AbstractCartFormStrategy implements CartPaymentOptionStrategyInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var bool
     */
    protected $testMode = false;

    /**
     * @return RouterInterface
     * @throws \Exception
     */
    public function getRouter()
    {
        if(!$this->router){
            throw new \Exception("Set Router first");
        }
        return $this->router;
    }

    /**
     * @param RouterInterface $router
     * @return AbstractPaymentOptionCartStrategy
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTestMode()
    {
        return $this->testMode;
    }

    /**
     * @param bool $flag
     * @return AbstractPaymentOptionCartStrategy
     */
    public function setTestMode($flag)
    {
        $this->testMode = $flag;
        return $this;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return bool
     */
    public function accept(CartInterface $cart, CartManager $cartManager)
    {
        return $cart->getPaymentOptionStrategyServiceId() == $this->getServiceId();
    }

    /**
     * @param CartInterface $cart
     */
    protected function removeStrategy(CartInterface $cart)
    {
        $cart->setPaymentOptionStrategyServiceId(null);
        $cart->setPaymentOptionStrategyServiceData(null);
    }
}