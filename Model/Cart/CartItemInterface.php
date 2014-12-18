<?php

namespace Ibrows\SyliusShopBundle\Model\Cart;

use DateTime;
use Ibrows\SyliusShopBundle\Model\Product\ProductInterface;
use Sylius\Bundle\CartBundle\Model\CartItemInterface as BaseCartItemInterface;

interface CartItemInterface extends BaseCartItemInterface
{
    /**
     * @return int
     */
    public function getReducedQuantity();

    /**
     * @param $quantity
     * @return int
     */
    public function setReducedQuantity($quantity);

    /**
     * @return bool
     */
    public function isDelivered();

    /**
     * @param bool $flag
     * @return CartItemInterface
     */
    public function setDelivered($flag = true);

    /**
     * @return DateTime
     */
    public function getDeliveredAt();

    /**
     * @return int
     */
    public function getQuantityNotAvailable();

    /**
     * @return CartItemInterface
     */
    public function setQuantityToAvailable();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return ProductInterface
     */
    public function getProduct();

    /**
     * @param ProductInterface $product
     * @return CartItemInterface
     */
    public function setProduct(ProductInterface $product = null);

    /**
     * @param ProductInterface $product
     * @return CartItemInterface
     */
    public function setProductObj(ProductInterface $product = null);

    /**
     * @return ProductInterface
     */
    public function getProductObj();

    /**
     * @return float
     */
    public function getTaxRate();

    /**
     * @param float $rate
     * @return CartItemInterface
     */
    public function setTaxRate($rate);

    /**
     * @return float
     */
    public function getTaxPrice();

    /**
     * @param float $price
     * @return CartItemInterface
     */
    public function setTaxPrice($price);

    /**
     * @return float
     */
    public function getTotalWithTaxPrice();

    /**
     * @param float $total
     * @return CartItemInterface
     */
    public function setTotalWithTaxPrice($total);

    /**
     * @return boolean
     */
    public function isTaxInclusive();

    /**
     * @param boolean $taxInclusive
     * @return CartItemInterface
     */
    public function setTaxInclusive($taxInclusive);

    /**
     * @return string
     */
    public function __toString();

    /**
     * @return int
     */
    public function getId();
}
