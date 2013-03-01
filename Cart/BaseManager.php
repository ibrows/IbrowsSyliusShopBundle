<?php

namespace Ibrows\SyliusShopBundle\Cart;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

use Sylius\Bundle\InventoryBundle\Checker\AvailabilityCheckerInterface;
use Sylius\Bundle\CartBundle\Provider\CartProviderInterface;
use Sylius\Bundle\CartBundle\Resolver\ItemResolverInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactory;

/**
 * @author marcsteiner
 *
 */
class BaseManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ObjectRepository
     */
    private $cartRepository;

    /**
     * @var ObjectManager
     */
    private $itemManager;

    /**
     * @var ObjectRepository
     */
    private $itemRepository;

    /**
     * Form factory.
     *
     * @var FormFactory
     */
    private $formFactory;

    /**
     * Stock availability checker.
     *
     * @var AvailabilityCheckerInterface
     */
    private $availabilityChecker;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var CartProviderInterface
     */
    private $provider;

    /**
     * @var ItemResolverInterface
     */
    private $resolver;

    /**
     * @param ObjectManager $om
     * @param CartProviderInterface $provider
     * @param ItemResolverInterface $resolver
     * @param FormFactory $formFactory
     * @param AvailabilityCheckerInterface $availabilityChecker
     * @param Request $request
     */
    public function __construct(ObjectManager $om, ObjectRepository $repo, ObjectManager $itemom, ObjectRepository $itemrepo, CartProviderInterface $provider, ItemResolverInterface $resolver,
            FormFactory $formFactory, AvailabilityCheckerInterface $availabilityChecker)
    {
        $this->resolver = $resolver;
        $this->provider = $provider;
        $this->cartRepository = $repo;
        $this->objectManager = $om;
        $this->itemRepository = $itemrepo;
        $this->itemManager = $itemom;
        $this->formFactory = $formFactory;
        $this->availabilityChecker = $availabilityChecker;

    }

    /**
     * @param CartInterface $cart
     * @return BaseManager
     */
    public function persistCart(CartInterface $cart)
    {
        $this->objectManager->persist($cart);
        $this->objectManager->flush();
        return $this;
    }

    public function resolve($item, Request $request)
    {
        return $this->resolver->resolve($item, $request);
    }

    public function createNewItem()
    {
        $class = $this->itemRepository->getClassName();
        return new $class();
    }

    public function addItem($item)
    {
        $this->getCurrentCart()->addItem($item);
        $this->saveCurrentCart();
    }

    public function removeItem($item)
    {
        $this->getCurrentCart()->removeItem($item);
        $this->saveCurrentCart();
    }

    public function clearCurrentCart()
    {
        $this->provider->abandonCart();
    }

    protected function refreshCurrentCart(){
        $this->getCurrentCart()->refreshCart();
    }

    protected function saveCurrentCart()
    {
        $this->refreshCurrentCart();
        $this->objectManager->persist($this->getCurrentCart());
        $this->objectManager->flush();
    }

    /**
     * Get current cart using the provider service.
     *
     * @return CartInterface
     */
    public function getCurrentCart()
    {
        return $this->provider->getCart();
    }

    /**
     * Get current cart using the provider service.
     *
     * @return CartInterface
     */
    public function setCurrentCart($cart)
    {
        $this->provider->setCart($cart);
        $this->saveCurrentCart();
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }

    public function getCartRepository()
    {
        return $this->cartRepository;
    }

    public function getItemManager()
    {
        return $this->itemManager;
    }

    public function getItemRepository()
    {
        return $this->itemRepository;
    }

}
