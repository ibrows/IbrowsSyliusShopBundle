<?php

namespace Ibrows\SyliusShopBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Sylius\Bundle\CartBundle\SyliusCartEvents;
use Sylius\Bundle\CartBundle\Event\FlashEvent;

/**
 * @Route("/cart")
 *
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
     *
     * @return array
     */
    public function summaryAction(Request $request)
    {
        $manager = $this->getCurrentCartManager();
        $cart = $manager->getCart();
        $form = $this->createForm('sylius_cart', $cart);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->persistCart($manager);

            /* @var $dispatcher EventDispatcherInterface */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(SyliusCartEvents::CART_SAVE_COMPLETED, new FlashEvent());
        }

        return array(
            'cart' => $cart,
            'form' => $form->createView(),
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
        $this->getCurrentCartManager()->clearCurrentCart();

        /* @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(SyliusCartEvents::CART_CLEAR_COMPLETED, new FlashEvent());

        return $this->forwardByRoute($this->getCartSummaryRoute());
    }
}
