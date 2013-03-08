<?php

namespace Ibrows\SyliusShopBundle\Model\Product;

use Sylius\Bundle\InventoryBundle\Model\StockableInterface;

interface ProductInterface extends StockableInterface
{
    /**
     * @return string
     */
    public function getName();
}