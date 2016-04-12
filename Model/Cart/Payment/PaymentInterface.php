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
     * @param bool          $stopPropagation
     *
     * @return $this
     */
    public function setCart(CartInterface $cart = null, $stopPropagation = false);

    /**
     * @return float
     */
    public function getAmount();

    /**
     * @param float $amount
     *
     * @return CartInterface
     */
    public function setAmount($amount = 0.0);

    /**
     * @return string
     */
    public function getStrategyId();

    /**
     * @param null $strategyId
     *
     * @return $this|PaymentInterface
     */
    public function setStrategyId($strategyId = null);

    /**
     * @return array
     */
    public function getStrategyData();

    /**
     * @param array $strategyData
     *
     * @return $this|PaymentInterface
     */
    public function setStrategyData(array $strategyData = null);

    /**
     * @return mixed
     */
    public function getData();

    /**
     * @param array $data
     *
     * @return CartInterface
     */
    public function setData(array $data = null);
}
