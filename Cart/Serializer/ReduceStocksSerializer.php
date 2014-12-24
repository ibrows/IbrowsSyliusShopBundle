<?php

/**
 * Created by PhpStorm.
 * Project: coffeeconnection
 *
 * User: mikemeier
 * Date: 24.12.14
 * Time: 14:05
 */

namespace Ibrows\SyliusShopBundle\Cart\Serializer;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\Stock\ReduceStocksStrategy;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Serializer\CartSerializerInterface;

class ReduceStocksSerializer implements CartSerializerInterface
{
    /**
     * @var CartManager
     */
    protected $cartManager;

    /**
     * @var ReduceStocksStrategy
     */
    protected $reduceStocksStrategy;

    /**
     * @param CartManager $cartManager
     * @param ReduceStocksStrategy $reduceStocksStrategy
     */
    public function __construct(CartManager $cartManager, ReduceStocksStrategy $reduceStocksStrategy = null)
    {
        if($reduceStocksStrategy){
            $reduceStocksStrategy->setEnabled(false);
        }else{
            $reduceStocksStrategy = new ReduceStocksStrategy();
        }

        $this->reduceStocksStrategy = $reduceStocksStrategy;
        $this->cartManager = $cartManager;
    }

    /**
     * @param CartInterface $cart
     * @return bool
     */
    public function accept(CartInterface $cart)
    {
        if (!$cart === $this->cartManager->getCart(false)) {
            return false;
        }
        return $this->reduceStocksStrategy->accept($cart, $this->cartManager);
    }

    /**
     * @param CartInterface $cart
     * @return void
     */
    public function serialize(CartInterface $cart)
    {
        $this->reduceStocksStrategy->compute($cart, $this->cartManager);
    }
}