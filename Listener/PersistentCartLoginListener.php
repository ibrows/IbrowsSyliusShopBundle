<?php

namespace Ibrows\SyliusShopBundle\Listener;

use Ibrows\SyliusShopBundle\Cart\CurrentCartManager;
use Ibrows\SyliusShopBundle\Model\User\UserInterface;
use Sylius\Bundle\CartBundle\Storage\CartStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class PersistentCartLoginListener
{
    /**
     * @var CartStorageInterface
     */
    private $cartStorageInterface;

    /**
     * @var CurrentCartManager
     */
    private $currentCartManager;

    /**
     * @var bool
     */
    private $persistentBasket;

    /**
     * @param CartStorageInterface $cartStorageInterface
     * @param CurrentCartManager $currentCartManager
     * @param bool $persistentBasket
     */
    public function __construct(
        CartStorageInterface $cartStorageInterface,
        CurrentCartManager $currentCartManager,
        $persistentBasket = false
    )
    {
        $this->cartStorageInterface = $cartStorageInterface;
        $this->currentCartManager = $currentCartManager;
        $this->persistentBasket = $persistentBasket;
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onLogin(InteractiveLoginEvent $event)
    {
        if (!$this->persistentBasket) {
            return;
        }

        if (
            !($token = $event->getAuthenticationToken()) ||
            !($user = $token->getUser()) ||
            !$user instanceof UserInterface
        ) {
            return;
        }

        // check if we have a current cart identifier and cart is locked
        if ($this->cartStorageInterface->getCurrentCartIdentifier() &&
            ($cart = $this->currentCartManager->getCart()) && $cart->isLocked()
        ) {
            return;
        }

        // at this point we have a cart which is not locked which we can abandon
        $this->currentCartManager->clearCurrentCart();

        // this creates a new cart or loads one from the database
        // if the conditions for a persitent cart are met.
        $this->currentCartManager->getCart();
    }
}