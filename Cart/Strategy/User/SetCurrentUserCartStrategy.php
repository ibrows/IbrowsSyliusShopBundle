<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\User;

use FOS\UserBundle\Model\UserInterface;
use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\AbstractCartStrategy;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\UserCartInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class SetCurrentUserCartStrategy extends AbstractCartStrategy
{
    protected $securityContext;

    /**
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return bool
     */
    public function accept(CartInterface $cart, CartManager $cartManager)
    {
        return $cart instanceof UserCartInterface && !$cart->getUser();
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return AdditionalCartItemInterface[]
     */
    public function compute(CartInterface $cart, CartManager $cartManager)
    {
        if(!$token = $this->securityContext->getToken()){
            return array();
        }
        if(!$user = $token->getUser()){
            return array();
        }
        if(!$user instanceof UserInterface){
            return array();
        }
        $cart->setUser($user);
        return array();
    }
}