<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Strategy;

use Symfony\Component\HttpFoundation\RedirectResponse;

interface CartPaymentStrategyResponseInterface
{
    /**
     * @return bool
     */
    public function isRedirect();

    /**
     * @param bool $flag
     * @return CartPaymentStrategyResponseInterface
     */
    public function setIsRedirect($flag);

    /**
     * @return RedirectResponse
     */
    public function getRedirectResponse();

    /**
     * @param RedirectResponse $response
     * @return CartPaymentStrategyResponseInterface
     */
    public function setRedirectResponse(RedirectResponse $response);
}