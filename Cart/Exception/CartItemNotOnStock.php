<?php

namespace Ibrows\SyliusShopBundle\Cart\Exception;

use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;

class CartItemNotOnStock
{
    /**
     * @var CartItemInterface
     */
    protected $item;

    /**
     * @param CartItemInterface $item
     */
    public function __construct(CartItemInterface $item)
    {
        $this->item = $item;
    }

    public function getOnHand()
    {
        return $this->item->getProduct()->getOnHand();
    }

    /**
     * @return CartItemInterface
     */
    public function getItem()
    {
        return $this->item;
    }
}