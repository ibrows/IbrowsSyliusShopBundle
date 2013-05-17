<?php

namespace Ibrows\SyliusShopBundle\Cart;

use Doctrine\Common\Collections\ArrayCollection;
use Ibrows\SyliusShopBundle\Entity\AdditionalCartItem;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;
use Sylius\Bundle\InventoryBundle\Checker\AvailabilityCheckerInterface;
use Ibrows\SyliusShopBundle\Cart\Exception\CartItemNotOnStockException;
use Ibrows\SyliusShopBundle\Cart\Exception\CartException;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Sylius\Bundle\CartBundle\Resolver\ItemResolverInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartStrategyInterface;
use Doctrine\Common\Collections\Collection;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartDeliveryOptionStrategyInterface;

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
     * @var ObjectRepository
     */
    protected $additionalItemObjectRepo;

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
     * @var CartStrategyInterface[]|Collection
     */
    protected $strategies;

    /**
     * @param ObjectManager $cartObjectManager
     * @param ObjectRepository $cartObjectRepo
     * @param ObjectManager $itemObjectManager
     * @param ObjectRepository $itemObjectRepo
     * @param ObjectRepository $additionalItemObjectRepo
     * @param ItemResolverInterface $resolver
     * @param AvailabilityCheckerInterface $availablityChecker
     */
    public function __construct(
        ObjectManager $cartObjectManager,
        ObjectRepository $cartObjectRepo,
        ObjectManager $itemObjectManager,
        ObjectRepository $itemObjectRepo,
        ObjectRepository $additionalItemObjectRepo,
        ItemResolverInterface $resolver,
        AvailabilityCheckerInterface $availablityChecker
    ){
        $this->cartObjectManager = $cartObjectManager;
        $this->cartObjectRepo = $cartObjectRepo;
        $this->itemObjectManager = $itemObjectManager;
        $this->itemObjectRepo = $itemObjectRepo;
        $this->additionalItemObjectRepo = $additionalItemObjectRepo;
        $this->resolver = $resolver;
        $this->availabilityChecker = $availablityChecker;
        $this->strategies = new ArrayCollection();
    }

    /**
     * @param CartStrategyInterface $strategy
     * @return CartManager
     */
    public function addStrategy(CartStrategyInterface $strategy)
    {
        $this->strategies->add($strategy);
        return $this;
    }

    /**
     * @param CartStrategyInterface $strategy
     * @return CartManager
     */
    public function removeStrategy(CartStrategyInterface $strategy)
    {
        $this->strategies->removeElement($strategy);
        return $this;
    }

    /**
     * @return Collection|CartStrategyInterface[]
     */
    public function getStrategies()
    {
        return $this->strategies;
    }

    /**
     * @return CartDeliveryOptionStrategyInterface[]
     */
    public function getDeliveryOptionStrategies()
    {
        $strategies = array();
        foreach($this->strategies as $strategy){
            if(
                $strategy instanceof CartDeliveryOptionStrategyInterface &&
                $strategy->isPossible($this->getCart(), $this)
            ){
                $strategies[] = $strategy;
            }
        }
        return $strategies;
    }

    /**
     * @param AdditionalCartItem $item
     * @return CartManager
     */
    public function addAdditionalItem(AdditionalCartItem $item)
    {
        $this->getCart(true)->addAdditionalItem($item);
        return $this;
    }

    /**
     * @param CartItemInterface $item
     * @return CartManager
     */
    public function addItem(CartItemInterface $item)
    {
        $this->getCart(true)->addItem($item);
        $this->refreshCart();
        return $this;
    }

    /**
     * @param CartItemInterface $item
     * @return CartManager
     */
    public function removeItem(CartItemInterface $item)
    {
        $this->getCart(true)->removeItem($item);
        $this->refreshCart();
        return $this;
    }

    /**
     * @param AdditionalCartItemInterface $item
     * @return CartManager
     */
    public function removeAdditionalItem(AdditionalCartItemInterface $item)
    {
        $this->getCart(true)->removeAdditionalItem($item);
        return $this;
    }

    /**
     * @param bool $refreshAndCheckAvailability
     * @return CartManager
     */
    public function persistCart($refreshAndCheckAvailability = true)
    {
        $cart = $this->getCart(true);

        if(true === $refreshAndCheckAvailability){
            $this->refreshCart();
            $this->checkAvailability();
        }

        $om = $this->cartObjectManager;
        $om->persist($cart);
        $om->flush();

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
     * @return string
     */
    public function getAdditionalCartItemClassName()
    {
        return $this->additionalItemObjectRepo->getClassName();
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
     * @param CartStrategyInterface $strategy
     * @return AdditionalCartItemInterface
     */
    public function createNewAdditionalCartItem(CartStrategyInterface $strategy)
    {
        $class = $this->getAdditionalCartItemClassName();

        /* @var $item AdditionalCartItemInterface */
        $item = new $class();
        $item->setStrategyIdentifier($strategy->getServiceId());

        return $item;
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

    public function refreshCart(){
        $cart = $this->getCart(true);
        if($cart->isClosed()){
            throw new \BadMethodCallException("Cart is already closed");
        }
        $this->computeStrategies();
    }

    public function computeStrategies()
    {
        $cart = $this->getCart(true);
        if($cart->isClosed()){
            throw new \BadMethodCallException("Cart is already closed");
        }

        foreach($this->strategies as $strategy){
            foreach($cart->getAdditionalItemsByStrategy($strategy) as $item){
                $this->removeAdditionalItem($item);
            }
        }

        $cart->refreshCart();

        foreach($this->strategies as $strategy){
            if($strategy->accept($cart, $this)){
                foreach($strategy->compute($cart, $this) as $item){
                    $this->addAdditionalItem($item);
                }
                $cart->refreshCart();
            }
        }
    }
}
