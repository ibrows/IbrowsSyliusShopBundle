<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartStrategyInterface;

abstract class AbstractCartStrategy implements CartStrategyInterface
{
    /**
     * @var string
     */
    protected $additionalCartItemClass;

    /**
     * @var string
     */
    protected $serviceId;

    /**
     * @return string
     */
    public function getAdditionalCartItemClass()
    {
        return $this->additionalCartItemClass;
    }

    /**
     * @param string $additionalCartItemClass
     * @return AbstractCartStrategy
     */
    public function setAdditionalCartItemClass($additionalCartItemClass)
    {
        $this->additionalCartItemClass = $additionalCartItemClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @param string $serviceId
     * @return AbstractCartStrategy
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;
        return $this;
    }

    /**
     * @param CartInterface $cart
     */
    protected function removeAdditionCartItems(CartInterface $cart)
    {
        if($items = $cart->getAdditionalItemsByStrategy($this)){
            foreach($items as $item){
                $cart->removeAdditionalItem($item);
            }
        }
    }

    /**
     * @return AdditionalCartItemInterface
     */
    protected function createAdditionalCartItem()
    {
        $className = $this->additionalCartItemClass;

        /* @var AdditionalCartItemInterface $item */
        $item = new $className();
        $item->setStrategyIdentifier($this->getServiceId());

        return $item;
    }
}