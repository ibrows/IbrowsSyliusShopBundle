<?php

namespace Ibrows\SyliusShopBundle\Event;

use Ibrows\SyliusShopBundle\Model\Cart\UserCartInterface;
use Ibrows\SyliusShopBundle\Model\User\UserInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Created by PhpStorm.
 * User: mikemeier
 * Date: 14.04.16
 * Time: 12:09
 */
class PersistentCartClonedEvent extends Event
{
    const NAME = 'sylius_shop.persistentcart.cloned';

    /**
     * @var UserCartInterface
     */
    private $originalCart;

    /**
     * @var UserCartInterface
     */
    private $clonedCart;

    /**
     * @var UserInterface
     */
    private $user;

    /**
     * PersistentCartClonedEvent constructor.
     * @param UserCartInterface $originalCart
     * @param UserCartInterface $clonedCart
     * @param UserInterface $user
     */
    public function __construct(UserCartInterface $originalCart, UserCartInterface $clonedCart, UserInterface $user)
    {
        $this->originalCart = $originalCart;
        $this->clonedCart = $clonedCart;
        $this->user = $user;
    }

    /**
     * @return UserCartInterface
     */
    public function getOriginalCart()
    {
        return $this->originalCart;
    }

    /**
     * @return UserCartInterface
     */
    public function getClonedCart()
    {
        return $this->clonedCart;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param UserCartInterface $clonedCart
     */
    public function setClonedCart(UserCartInterface $clonedCart = null)
    {
        $this->clonedCart = $clonedCart;
    }
}