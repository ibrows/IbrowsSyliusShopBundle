<?php

namespace Ibrows\SyliusShopBundle\Cart;
use Sylius\Bundle\CartBundle\Model\CartItemInterface;

use Sylius\Bundle\InventoryBundle\Checker\AvailabilityCheckerInterface;

use Symfony\Component\Form\FormFactory;

use Doctrine\Common\Persistence\ObjectRepository;

use Sylius\Bundle\ResourceBundle\Model\RepositoryInterface;

use Sylius\Bundle\CartBundle\Model\CartInterface;

use Doctrine\Common\Persistence\ObjectManager;

use Sylius\Bundle\CartBundle\Provider\CartProviderInterface;

use Sylius\Bundle\CartBundle\Resolver\ItemResolverInterface;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author marcsteiner
 *
 */
class BaseManager
{

    /**
     * @var ObjectManager
     */
    private $cartManager;

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
        $this->cartManager = $om;
        $this->itemRepository = $itemrepo;
        $this->itemManager = $itemom;
        $this->formFactory = $formFactory;
        $this->availabilityChecker = $availabilityChecker;

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
        $this->cartManager->remove($this->getCurrentCart());
        $this->cartManager->flush();
        $this->provider->abandonCart();
    }

    protected function refreshCurrentCart(){
        $this->getCurrentCart()->refreshCart();
    }

    protected function saveCurrentCart()
    {
        $this->refreshCurrentCart();
        $this->cartManager->persist($this->getCurrentCart());
        $this->cartManager->flush();
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

    public function getCartManager()
    {
        return $this->cartManager;
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
