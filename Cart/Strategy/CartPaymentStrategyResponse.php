<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy;

use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartPaymentStrategyResponseInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CartPaymentStrategyResponse implements CartPaymentStrategyResponseInterface
{
    /**
     * @return bool
     */
    public function isRedirect()
    {
        // TODO: Implement isRedirect() method.
    }

    /**
     * @return RedirectResponse
     */
    public function getRedirectResponse()
    {

    }
}