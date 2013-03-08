<?php

namespace Ibrows\SyliusShopBundle\Cart;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;

use Sylius\Bundle\InventoryBundle\Checker\AvailabilityCheckerInterface;

use Symfony\Component\HttpFoundation\Request;

use Sylius\Bundle\CartBundle\Provider\CartProviderInterface;
use Sylius\Bundle\CartBundle\Resolver\ItemResolverInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

class CurrentCartManager extends CartManager
{
    /**
     * @var CartProviderInterface
     */
    protected $provider;

    /**
     * @param ObjectManager $cartManager
     * @param ObjectRepository $cartRepo
     * @param ObjectManager $itemManager
     * @param ObjectRepository $itemRepo
     * @param ItemResolverInterface $resolver
     * @param AvailabilityCheckerInterface $availablityChecker
     * @param CartProviderInterface $provider
     */
    public function __construct(
        ObjectManager $cartManager,
        ObjectRepository $cartRepo,
        ObjectManager $itemManager,
        ObjectRepository $itemRepo,
        ItemResolverInterface $resolver,
        AvailabilityCheckerInterface $availablityChecker,
        CartProviderInterface $provider
    ){
        parent::__construct($cartManager, $cartRepo, $itemManager, $itemRepo, $resolver, $availablityChecker);

        $this->provider = $provider;
        parent::setCart($provider->getCart());
    }

    /**
     * @param CartInterface $cart
     * @return void
     * @throws \BadMethodCallException
     */
    public function setCart(CartInterface $cart = null)
    {
        throw new \BadMethodCallException("You dont want to change the current cart");
    }

    /**
     * @throws \BadMethodCallException
     * @return CartManager
     */
    public function closeCart()
    {
        $cart = $this->getCart();
        if($cart->isClosed()){
            throw new \BadMethodCallException("Cart is already closed");
        }

        // TODO serialize items - save all infos from ProductInterface to Cart

        $cart->setClosed();
        $this->clearCurrentCart();
        return $this;
    }

    /**
     * @return CurrentCartManager
     */
    public function clearCurrentCart()
    {
        $this->provider->abandonCart();
        parent::setCart(null);
        return $this;
    }
}
