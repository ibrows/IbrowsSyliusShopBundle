<?php

namespace Ibrows\SyliusShopBundle\Model\Cart;

interface AdditionalCartItemInterface
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * @return CartInterface
     */
    public function getCart();

    /**
     * @param CartInterface
     * @return AdditionalCartItemInterface
     */
    public function setCart(CartInterface $cart = null);

    /**
     * @return string
     */
    public function getStrategyIdentifier();

    /**
     * @param string $identifier
     * @return AdditionalCartItemInterface
     */
    public function setStrategyIdentifier($identifier);

    /**
     * @return array
     */
    public function getStrategyData();

    /**
     * @param array $data
     * @return AdditionalCartItemInterface
     */
    public function setStrategyData(array $data);

    /**
     * @return float
     */
    public function getPrice();

    /**
     * @param float $price
     * @return AdditionalCartItemInterface
     */
    public function setPrice($price);

    /**
     * @return string
     */
    public function getText();

    /**
     * @param string $text
     * @return AdditionalCartItemInterface
     */
    public function setText($text);

    /**
     * @return float
     */
    public function getTaxRate();

    /**
     * @param float $rate
     * @return AdditionalCartItemInterface
     */
    public function setTaxRate($rate);

    /**
     * @return float
     */
    public function getTaxPrice();

    /**
     * @param float $price
     * @return AdditionalCartItemInterface
     */
    public function setTaxPrice($price);

    /**
     * @return float
     */
    public function getPriceWithTax();

    /**
     * @param float $total
     * @return AdditionalCartItemInterface
     */
    public function setPriceWithTax($price);

    public function calculateTotal();
}