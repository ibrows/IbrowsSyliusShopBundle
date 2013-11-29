<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Delivery;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\AbstractCartFormStrategy;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartDeliveryOptionStrategyInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class AbstractDeliveryCartStrategy extends AbstractCartFormStrategy implements CartDeliveryOptionStrategyInterface
{
    /**
     * @var bool
     */
    protected $default = false;

    /**
     * @var mixed
     */
    protected $deliveryConditions;

    /**
     * @var bool
     */
    protected $skip = false;

    /**
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @param boolean $flag
     * @return AbstractDeliveryCartStrategy
     */
    public function setDefault($flag = true)
    {
        $this->default = $flag;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeliveryConditions()
    {
        return $this->deliveryConditions;
    }

    /**
     * @param mixed $deliveryConditions
     * @return AbstractDeliveryCartStrategy
     */
    public function setDeliveryConditions($deliveryConditions)
    {
        $this->deliveryConditions = $deliveryConditions;
        return $this;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return bool
     */
    public function accept(CartInterface $cart, CartManager $cartManager)
    {
        $selectedServiceId = $cart->getDeliveryOptionStrategyServiceId();
        if($selectedServiceId == $this->getServiceId()){
            return true;
        }

        if(!$selectedServiceId && $this->isDefault()){
            $cart->setDeliveryOptionStrategyServiceId($this->getServiceId());
            return true;
        }

        return false;
    }

    /**
     * @return boolean
     */
    public function isSkip()
    {
        return $this->skip;
    }

    /**
     * @param boolean $skip
     * @return AbstractDeliveryCartStrategy
     */
    public function setSkip($skip)
    {
        $this->skip = $skip;
        return $this;
    }

    /**
     * @param CartInterface $cart
     */
    protected function removeStrategy(CartInterface $cart)
    {
        $cart->setDeliveryOptionStrategyServiceId(null);
        $cart->setDeliveryOptionStrategyServiceData(null);
    }
}