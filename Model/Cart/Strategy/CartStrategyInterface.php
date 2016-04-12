<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Strategy;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Doctrine\Common\Persistence\ObjectRepository;

interface CartStrategyInterface
{
    /**
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     *
     * @return bool
     */
    public function accept(CartInterface $cart, CartManager $cartManager);

    /**
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     *
     * @return AdditionalCartItemInterface[]
     */
    public function compute(CartInterface $cart, CartManager $cartManager);

    /**
     * @param ObjectRepository $repo
     *
     * @return ObjectRepository
     */
    public function setAdditionalCartItemRepo(ObjectRepository $repo);

    /**
     * @return ObjectRepository
     */
    public function getAdditionalCartItemRepo();

    /**
     * @return string
     */
    public function getServiceId();

    /**
     * @param string $id
     *
     * @return CartStrategyInterface
     */
    public function setServiceId($id);

    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @param bool $flag
     *
     * @return mixed
     */
    public function setEnabled($flag);
}
