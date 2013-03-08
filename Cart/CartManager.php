<?php

namespace Ibrows\SyliusShopBundle\Cart;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;

use Sylius\Bundle\InventoryBundle\Checker\AvailabilityCheckerInterface;

use Ibrows\SyliusShopBundle\Cart\Exception\CartItemNotOnStockException;

use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

use Sylius\Bundle\CartBundle\Resolver\ItemResolverInterface;

class CartManager
{
    /**
     * @var ObjectManager
     */
    protected $cartObjectManager;

    /**
     * @var ObjectRepository
     */
    protected $cartObjectRepo;

    /**
     * @var ObjectManager
     */
    protected $itemObjectManager;

    /**
     * @var ObjectRepository
     */
    protected $itemObjectRepo;

    /**
     * @var ItemResolverInterface
     */
    protected $resolver;

    /**
     * @var AvailabilityCheckerInterface
     */
    protected $availabilityChecker;

    /**
     * @var CartInterface
     */
    protected $cart;

    /**
     * @param ObjectManager $cartObjectManager
     * @param ObjectRepository $cartObjectRepo
     * @param ObjectManager $itemObjectManager
     * @param ObjectRepository $itemObjectRepo
     * @param ItemResolverInterface $resolver
     * @param AvailabilityCheckerInterface $availablityChecker
     */
    public function __construct(
        ObjectManager $cartObjectManager,
        ObjectRepository $cartObjectRepo,
        ObjectManager $itemObjectManager,
        ObjectRepository $itemObjectRepo,
        ItemResolverInterface $resolver,
        AvailabilityCheckerInterface $availablityChecker
    ){
        $this->cartObjectManager = $cartObjectManager;
        $this->cartObjectRepo = $cartObjectRepo;
        $this->itemObjectManager = $itemObjectManager;
        $this->itemObjectRepo = $itemObjectRepo;
        $this->resolver = $resolver;
        $this->availabilityChecker = $availablityChecker;
    }

    /**
     * @param CartItemInterface $item
     * @return CartManager
     */
    public function addItem(CartItemInterface $item)
    {
        $this->getCart(true)->addItem($item);
        return $this;
    }

    /**
     * @param CartItemInterface $item
     * @return CartManager
     */
    public function removeItem(CartItemInterface $item)
    {
        $this->getCart(true)->removeItem($item);
        return $this;
    }

    /**
     * @return CartManager
     */
    public function persistCart()
    {
        $this->refreshCart();
        $this->checkAvailability();

        $this->cartObjectManager->persist($this->getCart(true));
        $this->cartObjectManager->flush();

        return $this;
    }

    /**
     * @return bool
     * @throws CartItemNotOnStockException
     */
    public function checkAvailability()
    {
        $notOnStockItems = array();

        foreach($this->getCart(true)->getItems() as $item){
            if(!$this->availabilityChecker->isStockSufficient($item->getProduct(), $item->getQuantity())){
                $notOnStockItems[] = $item;
            }
        }

        if(count($notOnStockItems) > 0){
            throw new CartItemNotOnStockException($notOnStockItems);
        }

        return true;
    }

    /**
     * @return string
     */
    public function getCartItemClassName()
    {
        return $this->itemObjectRepo->getClassName();
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
     * @return CartItemInterface
     */
    public function createNewItem()
    {
        $class = $this->getCartItemClassName();
        return new $class();
    }

    /**
     * @param CartInterface $cart
     * @return CartManager
     */
    public function setCart(CartInterface $cart = null)
    {
        $this->cart = $cart;
        return $this;
    }

    /**
     * @param bool $throwException
     * @return CartInterface
     * @throws \BadMethodCallException
     */
    public function getCart($throwException = false)
    {
        if(!$this->cart && true === $throwException){
            throw new \BadMethodCallException("Use setCart first!");
        }
        return $this->cart;
    }

    /**
     * @return ObjectManager
     */
    public function getCartObjectManager()
    {
        return $this->cartObjectManager;
    }

    /**
     * @return ObjectRepository
     */
    public function getCartObjectRepo()
    {
        return $this->cartObjectRepo;
    }

    /**
     * @return ObjectManager
     */
    public function getItemObjectManager()
    {
        return $this->itemObjectManager;
    }

    /**
     * @return ObjectRepository
     */
    public function getItemObjectRepo()
    {
        return $this->itemObjectRepo;
    }

    /**
     * @return CartManager
     */
    protected function refreshCart(){
        $this->getCart(true)->refreshCart();
        return $this;
    }
}
