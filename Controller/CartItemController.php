<?php

namespace Ibrows\SyliusShopBundle\Controller;

use Ibrows\SyliusShopBundle\Entity\Cart;

use Ibrows\SyliusShopBundle\Entity\CartItem;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Sylius\Bundle\CartBundle\SyliusCartEvents;
use Sylius\Bundle\CartBundle\Event\CartItemEvent;
use Sylius\Bundle\CartBundle\Event\FlashEvent;
use Sylius\Bundle\CartBundle\Resolver\ItemResolvingException;

/**
 * @Route("/cartitem")
 * @author marcsteiner
 *
 */
class CartItemController extends AbstractController
{

    protected $bundlePrefix = 'sylius_cart';
    protected $resourceName = 'item';
    /**
     * Adds item to cart.
     * It uses the resolver service so you can populate the new item instance
     * with proper values based on current request.
     *
     * It redirect to cart summary page by default.
     *
     * @param Request $request
     * @Route("/add", name="cart_item_add")
     * @return Response
     */
    public function addAction(Request $request)
    {
        $cart = $this->getCurrentCart();

        $item = new CartItem();
        $manager = $this->getManager();
        /* @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');

        try {
            $item = $this->getResolver()->resolve($item, $request);
        } catch (ItemResolvingException $exception) {
           $dispatcher->dispatch(SyliusCartEvents::ITEM_ADD_ERROR, new FlashEvent($exception->getMessage()));
           throw $exception;
        }

        $cart->addItem($item);
        $manager->persist($cart);
        $manager->flush();
        $manager->clear();
        $dispatcher->dispatch(SyliusCartEvents::ITEM_ADD_COMPLETED, new FlashEvent());

        return $this->forwardByRoute($this->getCartSummaryRoute());


    }

    /**
     * Removes item from cart.
     * It takes an item id as an argument.
     *
     * If the item is found and the current user cart contains that item,
     * it will be removed and the cart - refreshed and saved.
     *
     * @param Request $request
     * @Route("/remove/{id}", name="cart_item_remove")
     * @return Response
     */
    public function removeAction($id)
    {
        $cart = $this->getCurrentCart();
        $item = $this->findOr404();


        $manager = $this->getDoctrine()->getManagerForClass('Ibrows\SyliusShopBundle\Entity\Cart');

        /* @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');

        if (!$item || false === $cart->hasItem($item)) {
            $dispatcher->dispatch(SyliusCartEvents::ITEM_REMOVE_ERROR, new FlashEvent());
            throw new \Exception('item not found: '. $id);
        }

        $cart->removeItem($item);
        $manager->persist($cart);
        $manager->flush();

        $dispatcher->dispatch(SyliusCartEvents::ITEM_REMOVE_COMPLETED, new FlashEvent());

        return $this->forwardByRoute($this->getCartSummaryRoute());
    }

}
