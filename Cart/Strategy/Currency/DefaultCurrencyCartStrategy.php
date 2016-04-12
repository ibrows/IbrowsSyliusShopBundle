<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Currency;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

class DefaultCurrencyCartStrategy extends AbstractCurrencyCartStrategy
{
    /**
     * @var string
     */
    protected $defaultCurrency;

    /**
     * @param string $defaultCurrency
     */
    public function __construct($defaultCurrency)
    {
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     *
     * @return bool
     */
    public function acceptAsDefaultCurrency(CartInterface $cart, CartManager $cartManager)
    {
        return true;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     *
     * @return string
     */
    public function getDefaultCurrency(CartInterface $cart, CartManager $cartManager)
    {
        return $this->defaultCurrency;
    }

    /**
     * @param string        $fromCurrency
     * @param string        $toCurrency
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     *
     * @return bool
     */
    public function acceptCurrencyChange($fromCurrency, $toCurrency, CartInterface $cart, CartManager $cartManager)
    {
        return false;
    }

    /**
     * @param string        $fromCurrency
     * @param string        $toCurrency
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     *
     * @throws \LogicException
     */
    public function changeCurrency($fromCurrency, $toCurrency, CartInterface $cart, CartManager $cartManager)
    {
        throw new \LogicException('this strategy can not change currency');
    }
}
