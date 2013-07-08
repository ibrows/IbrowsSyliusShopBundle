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
     * @return mixed
     */
    public function getData();

    /**
     * @param array $data
     * @return CartInterface
     */
    public function setData(array $data = null);

}