<?php

namespace Ibrows\SyliusShopBundle\Cart;

use Doctrine\Common\Collections\ArrayCollection;
use Ibrows\SyliusShopBundle\Cart\Strategy\Costs;
use Ibrows\SyliusShopBundle\Entity\AdditionalCartItem;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartCurrencyStrategyInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartPaymentOptionStrategyInterface;
use Sylius\Bundle\InventoryBundle\Checker\AvailabilityCheckerInterface;
use Ibrows\SyliusShopBundle\Cart\Exception\CartItemNotOnStockException;
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
     * @var CartCurrencyStrategyInterface[]|Collection
     */
    protected $currencyStrategies;

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
        $this->currencyStrategies = new ArrayCollection();
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
     * @param array|\Traversable $strategies
     * @return CartManager
     * @throws \InvalidArgumentException
     */
    public function setStrategies($strategies)
    {
        if(!is_array($strategies) && !$strategies instanceof \Traversable){
            throw new \InvalidArgumentException("strategies has to implement Traversable or array");
        }
        foreach($this->getStrategies() as $strategy){
            $this->removeStrategy($strategy);
        }
        foreach($strategies as $strategy){
            $this->addStrategy($strategy);
        }
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
    public function getPossibleDeliveryOptionStrategies()
    {
        $strategies = array();
        $cart = $this->getCart(true);
        foreach($this->strategies as $strategy){
            if(
                $strategy instanceof CartDeliveryOptionStrategyInterface &&
                $strategy->isPossible($cart, $this)
            ){
                $strategies[] = $strategy;
            }
        }
        return $strategies;
    }

    /**
     * @param string $strategyId
     * @return CartDeliveryOptionStrategyInterface
     */
    public function getPossibleDeliveryOptionStrategyById($strategyId)
    {
        foreach($this->getPossibleDeliveryOptionStrategies() as $strategy){
            if($strategy->getServiceId() == $strategyId){
                return $strategy;
            }
        }
        return null;
    }

    /**
     * @param string $strategyId
     * @return CartDeliveryOptionStrategyInterface
     */
    public function getPossiblePaymentOptionStrategyById($strategyId)
    {
        foreach($this->getPossiblePaymentOptionStrategies() as $strategy){
            if($strategy->getServiceId() == $strategyId){
                return $strategy;
            }
        }
        return null;
    }

    /**
     * @return CartPaymentOptionStrategyInterface[]
     */
    public function getPossiblePaymentOptionStrategies()
    {
        $strategies = array();
        $cart = $this->getCart(true);
        foreach($this->strategies as $strategy){
            if(
                $strategy instanceof CartPaymentOptionStrategyInterface &&
                $strategy->isPossible($cart, $this)
            ){
                $strategies[] = $strategy;
            }
        }
        return $strategies;
    }

    /**
     * @return CartPaymentOptionStrategyInterface
     */
    public function getSelectedPaymentOptionStrategyService()
    {
        $serviceId = $this->getCart()->getPaymentOptionStrategyServiceId();
        if(!$serviceId){
            return null;
        }
        return $this->getPossiblePaymentOptionStrategyById($serviceId);
    }

    /**
     * @return CartDeliveryOptionStrategyInterface
     */
    public function getSelectedDeliveryOptionStrategyService()
    {
        $serviceId = $this->getCart()->getDeliveryOptionStrategyServiceId();
        if(!$serviceId){
            return null;
        }
        return $this->getPossibleDeliveryOptionStrategyById($serviceId);
    }

    /**
     * @return array
     */
    public function getSelectedDeliveryOptionStrategyServiceCosts()
    {
        return $this->getStrategyServiceCostsByStrategy($this->getSelectedDeliveryOptionStrategyService());
    }

    /**
     * @return array
     */
    public function getSelectedPaymentOptionStrategyServiceCosts()
    {
        return $this->getStrategyServiceCostsByStrategy($this->getSelectedPaymentOptionStrategyService());
    }

    /**
     * @param CartStrategyInterface $strategy
     * @return array $costs
     */
    public function getStrategyServiceCostsByStrategy(CartStrategyInterface $strategy = null)
    {
        $costs = new Costs();
        if(!$strategy){
            return $costs;
        }

        foreach($this->getCart(true)->getAdditionalItemsByStrategy($strategy) as $item){
            $costs->setTotal($costs->getTotal()+$item->getPrice());
            $costs->setTax($costs->getTax()+$item->getTaxPrice());
            $costs->setTotalWithTax($costs->getTotalWithTax()+$item->getPriceWithTax());
        }

        return $costs;
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
            if(!$item->getProduct()->isEnabled()){
                $notOnStockItems[] = $item;
                continue;
            }
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
     * @throws \LogicException
     * @throws \BadMethodCallException
     * @return CartInterface
     */
    public function getCart($throwException = false)
    {
        if(!$cart = $this->cart){
            if(true === $throwException){
                throw new \BadMethodCallException("Use setCart first!");
            }else{
                return null;
            }
        }

        $hasCurrency = (bool)$cart->getCurrency();

        if(!$hasCurrency){
            foreach($this->currencyStrategies as $currencyStrategy){
                if($currencyStrategy->acceptAsDefaultCurrency($cart, $this)){
                    $cart->setCurrency($currencyStrategy->getDefaultCurrency($cart, $this));
                    $hasCurrency = true;
                    $this->persistCart();
                    break;
                }
            }
        }

        if(!$hasCurrency){
            throw new \LogicException("No currency set on cart and no strategy could provide it");
        }

        return $cart;
    }

    /**
     * @param string $toCurrency
     * @return CartManager
     * @throws \LogicException
     */
    public function changeCurrency($toCurrency)
    {
        $cart = $this->getCart(true);
        $fromCurrency = $cart->getCurrency();

        if($fromCurrency == $toCurrency){
            return $this;
        }

        $foundStrategy = false;

        foreach($this->currencyStrategies as $strategy){
            if($strategy->acceptCurrencyChange($fromCurrency, $toCurrency, $cart, $this)){
                $strategy->changeCurrency($fromCurrency, $toCurrency, $cart, $this);
                $foundStrategy = true;
                $this->persistCart();
            }
        }

        if(!$foundStrategy){
            throw new \LogicException("No currency strategy could provide currency change from $fromCurrency to $toCurrency");
        }

        return $this;
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
            if($strategy->isEnabled() && $strategy->accept($cart, $this)){
                foreach($strategy->compute($cart, $this) as $item){
                    $this->addAdditionalItem($item);
                }
                $cart->refreshCart();
            }
        }
    }

    /**
     * @return Collection|CartCurrencyStrategyInterface[]
     */
    public function getCurrencyStrategies()
    {
        return $this->currencyStrategies;
    }

    /**
     * @param CartCurrencyStrategyInterface $strategy
     * @return CartManager
     */
    public function addCurrencyStrategy(CartCurrencyStrategyInterface $strategy)
    {
        $this->currencyStrategies->add($strategy);
        return $this;
    }

    /**
     * @param CartCurrencyStrategyInterface $strategy
     * @return CartManager
     */
    public function removeCurrencyStrategy(CartCurrencyStrategyInterface $strategy)
    {
        $this->currencyStrategies->removeElement($strategy);
        return $this;
    }
}
