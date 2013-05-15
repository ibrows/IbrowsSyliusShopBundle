<?php

namespace Ibrows\SyliusShopBundle\Cart;

use Doctrine\Common\Collections\ArrayCollection;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

use Ibrows\SyliusShopBundle\Model\Cart\Serializer\CartSerializerInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Serializer\CartItemSerializerInterface;

use Sylius\Bundle\InventoryBundle\Checker\AvailabilityCheckerInterface;

use Symfony\Component\HttpFoundation\Request;

use Sylius\Bundle\CartBundle\Provider\CartProviderInterface;
use Sylius\Bundle\CartBundle\Resolver\ItemResolverInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Collections\Collection;

class CurrentCartManager extends CartManager
{
    /**
     * @var CartProviderInterface
     */
    protected $provider;

    /**
     * @var CartSerializerInterface[]|Collection
     */
    protected $cartSerializers;

    /**
     * @var CartItemSerializerInterface[]|Collection
     */
    protected $cartItemSerializers;

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

        $this->cartItemSerializers = new ArrayCollection();
        $this->cartSerializers = new ArrayCollection();
    }

    /**
     * @param CartSerializerInterface $serializer
     * @return CurrentCartManager
     */
    public function addCartSerializer(CartSerializerInterface $serializer)
    {
        $this->cartSerializers->add($serializer);
        return $this;
    }

    /**
     * @param CartSerializerInterface $serializer
     * @return CurrentCartManager
     */
    public function removeCartSerializer(CartSerializerInterface $serializer)
    {
        $this->cartSerializers->removeElement($serializer);
        return $this;
    }

    /**
     * @param CartItemSerializerInterface $serializer
     * @return CurrentCartManager
     */
    public function addCartItemSerializer(CartItemSerializerInterface $serializer)
    {
        $this->cartItemSerializers->add($serializer);
        return $this;
    }

    /**
     * @param CartItemSerializerInterface $serializer
     * @return CurrentCartManager
     */
    public function removeCartItemSerializer(CartItemSerializerInterface $serializer)
    {
        $this->cartItemSerializers->removeElement($serializer);
        return $this;
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
    public function closeCart($peristCart = true)
    {
        $cart = $this->getCart();
        if($cart->isClosed()){
            throw new \BadMethodCallException("Cart is already closed");
        }

        $this->serializeCartItems($cart);
        $this->serializeCart($cart);

        $cart->setClosed();

        if(true === $peristCart){
            $this->persistCart();
        }

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

    /**
     * @param CartInterface $cart
     */
    protected function serializeCart(CartInterface $cart)
    {
        foreach($this->cartSerializers as $serializer){
            if(true === $serializer->accept($cart)){
                $serializer->serialize($cart);
            }
        }
    }

    /**
     * @param CartInterface $cart
     */
    protected function serializeCartItems(CartInterface $cart)
    {
        foreach($cart->getItems() as $item){
            foreach($this->cartItemSerializers as $serializer){
                if(true === $serializer->accept($item)){
                    $serializer->serialize($item);
                }
            }
        }
    }
}
