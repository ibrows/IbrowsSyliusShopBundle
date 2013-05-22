<?php

namespace Ibrows\SyliusShopBundle\Model\Cart;

use Sylius\Bundle\CartBundle\Model\CartItemInterface as BaseCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Product\ProductInterface;

use DateTime;

interface CartItemInterface extends BaseCartItemInterface
{
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
     * @return int
     */
    public function getId();
}