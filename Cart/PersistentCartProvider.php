<?php

namespace Ibrows\SyliusShopBundle\Cart;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\NonUniqueResultException;
use Ibrows\SyliusShopBundle\Event\PersistentCartClonedEvent;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\UserCartInterface;
use Ibrows\SyliusShopBundle\Model\User\UserInterface;
use Sylius\Bundle\CartBundle\Entity\CartRepository;
use Sylius\Bundle\CartBundle\Provider\CartProvider as BaseCartProvider;
use Sylius\Bundle\CartBundle\Storage\CartStorageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PersistentCartProvider extends BaseCartProvider
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var bool
     */
    private $persistentBasket;

    /**
     * @param CartStorageInterface $storage
     * @param ObjectManager $manager
     * @param CartRepository $repository
     * @param TokenStorageInterface $tokenStorage
     * @param EventDispatcherInterface $eventDispatcher
     * @param bool $persistentBasket
     */
    public function __construct(
        CartStorageInterface $storage,
        ObjectManager $manager,
        CartRepository $repository,
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        $persistentBasket = false
    )
    {
        parent::__construct($storage, $manager, $repository);
        $this->tokenStorage = $tokenStorage;
        $this->persistentBasket = $persistentBasket;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return CartInterface
     */
    public function getCart()
    {
        if (!$this->persistentBasket) {
            return parent::getCart();
        }

        if (null !== $this->cart) {
            return $this->cart;
        }

        $cartIdentifier = $this->storage->getCurrentCartIdentifier();

        if ($cartIdentifier && $cart = $this->getCartByIdentifier($cartIdentifier)) {
            return $this->cart = $cart;
        }

        if (null === ($user = $this->getUser())) {
            return parent::getCart();
        }

        $cart = $this->findRecentCartForUser($user);

        if (!$cart instanceof UserCartInterface) {
            return parent::getCart();
        }

        if (
            $cart &&
            !($cart->isLocked() || $cart->isClosed() || $cart->isConfirmed() || $cart->isTermsAndConditions())
        ) {
            $clonedCart = $this->cloneCart($cart, $user);

            $clonedCartEvent = new PersistentCartClonedEvent($cart, $clonedCart, $user);
            $this->eventDispatcher->dispatch(PersistentCartClonedEvent::NAME, $clonedCartEvent);
            $clonedCart = $clonedCartEvent->getClonedCart();

            return $this->useCart($clonedCart);
        }

        return parent::getCart();
    }

    /**
     * @return null|UserInterface
     */
    private function getUser()
    {
        if (!$token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!$user = $token->getUser()) {
            return null;
        }

        return $user instanceof UserInterface ? $user : null;
    }

    /**
     * @param UserInterface $user
     * @return CartInterface|null
     * @throws NonUniqueResultException
     */
    private function findRecentCartForUser(UserInterface $user)
    {
        $cartQuery = $this->repository->createQueryBuilder('cart');
        $cartQuery->orderBy('cart.createdAt', 'DESC');
        $cartQuery->setMaxResults(1);
        $cartQuery->andWhere($cartQuery->expr()->eq('cart.user', ':user_id'));
        $cartQuery->setParameter('user_id', $user->getId());

        return $cartQuery->getQuery()->getOneOrNullResult();
    }

    /**
     * @param UserCartInterface $cart
     * @param UserInterface $user
     * @return UserCartInterface
     */
    private function cloneCart(UserCartInterface $cart, UserInterface $user)
    {
        /** @var UserCartInterface $newCart */
        $newCart = $this->repository->createNew();

        $newCart->setCurrency($cart->getCurrency());
        $newCart->setUser($user);
        $newCart->setDeliveryAddress($cart->getDeliveryAddress());
        $newCart->setInvoiceAddress($cart->getInvoiceAddress());
        $newCart->setEmail($cart->getEmail());
        $newCart->setDeliveryOptionStrategyServiceId($cart->getDeliveryOptionStrategyServiceId());
        $newCart->setDeliveryOptionStrategyServiceData($cart->getDeliveryOptionStrategyServiceData());
        $newCart->setPaymentOptionStrategyServiceId($cart->getPaymentOptionStrategyServiceId());
        $newCart->setPaymentOptionStrategyServiceData($cart->getPaymentOptionStrategyServiceData());

        foreach ($cart->getVoucherCodes() as $voucherCode) {
            $newCart->addVoucherCode($voucherCode);
        }

        /** @var CartItemInterface $item */
        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();

            $itemClass = get_class($item);
            /** @var CartItemInterface $newItem */
            $newItem = new $itemClass();

            $newItem->setQuantity($item->getQuantity());
            $newItem->setUnitPrice($item->getUnitPrice());
            $newItem->setTaxInclusive($item->isTaxInclusive());
            $newItem->setTaxRate($item->getTaxRate());
            $newItem->setProduct($product);

            $newCart->addItem($newItem);
        }

        return $newCart;
    }

    /**
     * @param CartInterface $cart
     * @return CartInterface
     */
    private function useCart(CartInterface $cart)
    {
        $this->manager->persist($cart);
        $this->manager->flush();
        $this->setCart($cart);
        return $cart;
    }
}
