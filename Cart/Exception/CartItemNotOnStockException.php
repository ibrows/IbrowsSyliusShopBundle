<?php

namespace Ibrows\SyliusShopBundle\Cart\Exception;

use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;

class CartItemNotOnStockException extends CartItemException
{
    /**
     * @var CartItemNotOnStock[]
     */
    protected $cartItemsNotOnStock = array();

    /**
     * @param array $notOnStockItems
     */
    public function __construct(array $notOnStockItems)
    {
        foreach ($notOnStockItems as $item) {
            $this->addNotOnStockItem($item);
        }
    }

    /**
     * @return CartItemNotOnStock[]
     */
    public function getCartItemsNotOnStock()
    {
        return $this->cartItemsNotOnStock;
    }

    /**
     * @param CartItemInterface $item
     */
    protected function addNotOnStockItem(CartItemInterface $item)
    {
        $this->cartItemsNotOnStock[] = new CartItemNotOnStock($item);
    }
}
