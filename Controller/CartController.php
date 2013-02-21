<?php

namespace Ibrows\SyliusShopBundle\Controller;
use Ibrows\SyliusShopBundle\Controller\AbstractController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Sylius\Bundle\CartBundle\SyliusCartEvents;
use Sylius\Bundle\CartBundle\Event\CartItemEvent;
use Sylius\Bundle\CartBundle\Event\FlashEvent;
use Sylius\Bundle\CartBundle\Resolver\ItemResolvingException;

/**
 * @Route("/cart")
 * @author marcsteiner
 * @author Mike Meier
 */
class CartController extends AbstractController
{
    /**
     * @Route("/", name="cart_summary")
     * @Template
     *
     * @param Request $request
     * @return array
     */
    public function summaryAction(Request $request)
    {
        $manager = $this->getCartManager();
        $cart = $this->getCurrentCart();
        $form = $this->createForm('sylius_cart', $cart);
        if ($request->getMethod() == 'POST' && $request->request->get('sylius_cart') != null && $form->bind($request)->isValid()) {
            $manager->setCurrentCart($cart);

            /* @var $dispatcher EventDispatcherInterface */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(SyliusCartEvents::CART_SAVE_COMPLETED, new FlashEvent());
        }

        return array(
            'cart' => $cart,
            'form' => $form->createView()
        );
    }

    /**
     * Clears the current cart using the operator.
     * By default it redirects to cart summary page.
     *
     * @return Response
     */
    public function clearAction()
    {
        $this->getCartManager()->clearCurrentCart();

        /* @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(SyliusCartEvents::CART_CLEAR_COMPLETED, new FlashEvent());

        return $this->forwardByRoute($this->getCartSummaryRoute());
    }
}
