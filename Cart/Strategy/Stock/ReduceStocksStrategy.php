<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Stock;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\AbstractCartStrategy;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;

class ReduceStocksStrategy extends AbstractCartStrategy
{


    public function __construct()
    {

    }

    /**
     * /**
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     * @return bool
     */
    public function accept(CartInterface $cart, CartManager $cartManager)
    {
        return $cartManager->getCart()->isConfirmed();
    }

    /**
     * @param CartItemInterface $item
     * @return bool
     */
    public function reduceItem(CartItemInterface $item)
    {
        if (!$item->getProduct()) {
            return false;
        }
        if ($item->getReducedQuantity() == $item->getQuantity()) {
            return false;
        }
        $newStock = $item->getProduct()->getOnHand() - $item->getQuantity() + $item->getReducedQuantity() ;
        $item->getProduct()->setOnHand($newStock);
        $item->setReducedQuantity($item->getQuantity());
        return true;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     * @return AdditionalCartItemInterface[]
     */
    public function compute(CartInterface $cart, CartManager $cartManager)
    {
        foreach ($cart->getItems() as $item) {
            $this->reduceItem($item);
        }
        return array();
    }
}