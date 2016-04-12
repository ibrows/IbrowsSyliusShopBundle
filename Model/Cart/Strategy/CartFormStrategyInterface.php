<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Strategy;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Cart\CartManager;
use Symfony\Component\Form\FormTypeInterface;

interface CartFormStrategyInterface extends CartStrategyInterface, FormTypeInterface, CartDefaultOptionStrategyInterface
{
    /**
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     *
     * @return bool
     */
    public function isPossible(CartInterface $cart, CartManager $cartManager);

    /**
     * @return string
     */
    public function __toString();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param CartInterface $cart
     *
     * @return string
     */
    public function getTranslationKey(CartInterface $cart);

    /**
     * @param CartInterface $cart
     *
     * @return array
     */
    public function getTranslationParameters(CartInterface $cart);

    /**
     * @return bool
     */
    public function isParentVisible();

    /**
     * @param bool $flag
     *
     * @return CartFormStrategyInterface
     */
    public function setParentVisible($flag);

    /**
     * @param string $domain
     *
     * @return CartFormStrategyInterface
     */
    public function setDefaultTranslationDomain($domain);

    /**
     * @return string
     */
    public function getDefaultTranslationDomain();

    /**
     * @return array
     */
    public function __sleep();
}
