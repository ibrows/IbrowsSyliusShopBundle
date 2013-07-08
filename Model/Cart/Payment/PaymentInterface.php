<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Payment;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

interface PaymentInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return CartInterface
     */
    public function getCart();

    /**
     * @param CartInterface $cart
     * @param bool $stopPropagation
     * @return $this
     */
    public function setCart(CartInterface $cart = null, $stopPropagation = false);

    /**
     * @return mixed
     */
    public function getData();

    /**
     * @param array $data
     * @return CartInterface
     */
    public function setData(array $data = null);

}