<?php

namespace Ibrows\SyliusShopBundle\Cart;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;

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

    public function __construct(
        ObjectManager $cartManager,
        ObjectRepository $cartRepo,
        ObjectManager $itemManager,
        ObjectRepository $itemRepo,
        ItemResolverInterface $resolver,
        CartProviderInterface $provider
    ){
        parent::__construct($cartManager, $cartRepo, $itemManager, $itemRepo, $resolver);

        $this->provider = $provider;
        $this->setCart($provider->getCart());
    }

    /**
     * @return CartManager
     */
    public function closeCart()
    {
        parent::closeCart();
        $this->clearCurrentCart();
        return $this;
    }

    /**
     * @return CurrentCartManager
     */
    public function clearCurrentCart()
    {
        $this->provider->abandonCart();
        return $this;
    }
}
