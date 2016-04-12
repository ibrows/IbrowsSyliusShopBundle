<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\User;

use FOS\UserBundle\Model\UserInterface;
use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\AbstractCartStrategy;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\UserCartInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SetCurrentUserCartStrategy extends AbstractCartStrategy
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     *
     * @return bool
     */
    public function accept(CartInterface $cart, CartManager $cartManager)
    {
        return $cart instanceof UserCartInterface && !$cart->getUser();
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     *
     * @return AdditionalCartItemInterface[]
     */
    public function compute(CartInterface $cart, CartManager $cartManager)
    {
        if ($cart instanceof UserCartInterface) {
            $cart->setUser($this->getUser());
        }
        return array();
    }

    /**
     * @return UserInterface|null
     */
    protected function getUser()
    {
        if (!$token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!$user = $token->getUser()) {
            return null;
        }

        if (!$user instanceof UserInterface) {
            return null;
        }

        return $user;
    }
}
