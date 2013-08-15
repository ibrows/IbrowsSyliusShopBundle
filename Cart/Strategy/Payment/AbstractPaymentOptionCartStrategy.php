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
     * @var bool
     */
    protected $default;

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
     * @param bool $flag
     * @return AbstractPaymentOptionCartStrategy
     */
    public function setDefault($flag = true)
    {
        $this->default = $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return (bool)$this->default;
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
        $selectedServiceId = $cart->getPaymentOptionStrategyServiceId();
        if($selectedServiceId == $this->getServiceId()){
            return true;
        }

        if(!$selectedServiceId && $this->isDefault()){
            $cart->setPaymentOptionStrategyServiceId($this->getServiceId());
            return true;
        }

        return false;
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