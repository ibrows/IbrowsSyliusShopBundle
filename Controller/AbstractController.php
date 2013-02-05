<?php

namespace Ibrows\SyliusShopBundle\Controller;

use Sylius\Bundle\CartBundle\Model\CartInterface;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class AbstractController extends Controller
{
    public static function refreshCart(CartInterface $cart)
    {
        $cart->calculateTotal();
        $cart->setTotalItems($cart->countItems());
        return $cart;
    }
    /**
     * Cart summary page route.
     *
     * @return string
     */
    protected function getCartSummaryRoute()
    {
        return 'sylius_cart_summary';
    }

    /**
     * Get current cart using the provider service.
     *
     * @return CartInterface
     */
    protected function getCurrentCart()
    {
        return $this
        ->getProvider()
        ->getCart()
        ;
    }

    /**
     * Get cart provider.
     *
     * @return CartProviderInterface
     */
    protected function getProvider()
    {
        return $this->get('sylius_cart.provider');
    }

    /**
     * Get cart item resolver.
     * This service is used to build the new cart item instance.
     *
     * @return CartResolverInterface
     */
    protected function getResolver()
    {
        return $this->get('sylius_cart.resolver');
    }

    protected function getProductRepository()
    {
        return $this->get('sylius.repository.product');
    }
}