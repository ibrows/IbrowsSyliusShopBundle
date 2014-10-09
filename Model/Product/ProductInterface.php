<?php

namespace Ibrows\SyliusShopBundle\Model\Product;

use Sylius\Bundle\InventoryBundle\Model\StockableInterface;

interface ProductInterface extends StockableInterface
{
    /**
     * @return float
     */
    public function getPrice();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return boolean
     */
    public function isEnabled();

    /**
     * @return string
     */
    public function __toString();
}