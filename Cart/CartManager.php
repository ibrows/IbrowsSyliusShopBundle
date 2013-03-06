<?php

namespace Ibrows\SyliusShopBundle\Cart;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;

use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

use Sylius\Bundle\CartBundle\Resolver\ItemResolverInterface;

class CartManager
{
    /**
     * @var ObjectManager
     */
    protected $cartManager;

    /**
     * @var ObjectRepository
     */
    protected $cartRepo;

    /**
     * @var ObjectManager
     */
    protected $itemManager;

    /**
     * @var ObjectRepository
     */
    protected $itemRepo;

    /**
     * @var ItemResolverInterface
     */
    protected $resolver;

    /**
     * @var CartInterface
     */
    protected $cart;

    /**
     * @param ObjectManager $cartManager
     * @param ObjectRepository $cartRepo
     * @param ObjectManager $itemManager
     * @param ObjectRepository $itemRepo
     * @param ItemResolverInterface $resolver
     */
    public function __construct(
        ObjectManager $cartManager,
        ObjectRepository $cartRepo,
        ObjectManager $itemManager,
        ObjectRepository $itemRepo,
        ItemResolverInterface $resolver
    ){
        $this->cartManager = $cartManager;
        $this->cartRepo = $cartRepo;
        $this->itemManager = $itemManager;
        $this->itemRepo = $itemRepo;
        $this->resolver = $resolver;
    }

    /**
     * @param CartItemInterface $item
     * @return CartManager
     */
    public function addItem(CartItemInterface $item)
    {
        $this->getCart()->addItem($item);
        return $this;
    }

    /**
     * @param CartItemInterface $item
     * @return CartManager
     */
    public function removeItem(CartItemInterface $item)
    {
        $this->getCart()->removeItem($item);
        return $this;
    }

    /**
     * @return CartManager
     */
    public function persistCart()
    {
        $cart = $this->getCart();
        $this->refreshCart($cart);

        $this->cartManager->persist($cart);
        $this->cartManager->flush();

        return $this;
    }

    /**
     * @param $item
     * @param Request $request
     * @return CartItemInterface
     */
    public function resolve($item, Request $request)
    {
        return $this->resolver->resolve($item, $request);
    }

    /**
     * @return CartManager
     */
    public function closeCart()
    {
        // TODO serialize items - save all infos from ProductInterface to Cart
        return $this;
    }

    /**
     * @return CartItemInterface
     */
    public function createNewItem()
    {
        $class = $this->itemRepository->getClassName();
        return new $class();
    }

    /**
     * @param CartInterface $cart
     * @return CartManager
     */
    public function setCart(CartInterface $cart)
    {
        $this->cart = $cart;
        return $this;
    }

    /**
     * @return CartInterface
     * @throws \BadMethodCallException
     */
    protected function getCart()
    {
        if(!$this->cart){
            throw new \BadMethodCallException("Use setCart first!");
        }
        return $this->cart;
    }

    /**
     * @return CartManager
     */
    protected function refreshCart(){
        $this->getCart()->refreshCart();
        return $this;
    }
}
