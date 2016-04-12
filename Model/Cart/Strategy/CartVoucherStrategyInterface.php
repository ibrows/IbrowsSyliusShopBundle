<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Strategy;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

interface CartVoucherStrategyInterface extends CartStrategyInterface
{
    /**
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     */
    public function redeemVouchers(CartInterface $cart, CartManager $cartManager);

    /**
     * @return bool True if multiple vouchers are cumulative, false otherwise
     */
    public function isCumulative();
}
