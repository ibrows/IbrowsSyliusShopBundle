<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Payment;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\ErrorRedirectResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\PaymentFinishedResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\SelfRedirectResponse;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class PosPaymentOptionCartStrategy
 * @package Ibrows\SyliusShopBundle\Cart\Strategy\Payment
 *
 * This payment service is coupled with a delivery strategy id
 * As an example: If SelfpickupDeliveryCartStrategy is selected, this option is valid for payment
 */
class PosPaymentOptionCartStrategy extends AbstractPaymentOptionCartStrategy
{
    /**
     * @var array
     */
    protected $allowedDeliveryServiceIds;

    /**
     * @param array $allowedDeliveryServiceIds
     */
    public function __construct(array $allowedDeliveryServiceIds = array())
    {
        $this->allowedDeliveryServiceIds = $allowedDeliveryServiceIds;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return bool
     */
    public function isPossible(CartInterface $cart, CartManager $cartManager)
    {
        return in_array($cartManager->getSelectedDeliveryOptionStrategyService()->getServiceId(), $this->allowedDeliveryServiceIds);
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return AdditionalCartItemInterface[]
     */
    public function compute(CartInterface $cart, CartManager $cartManager)
    {
        return array();
    }

    /**
     * @param Context $context
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return RedirectResponse|PaymentFinishedResponse|ErrorRedirectResponse|SelfRedirectResponse
     */
    public function pay(Context $context, CartInterface $cart, CartManager $cartManager)
    {
        return new PaymentFinishedResponse($this->getServiceId(), PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_COMPLETION);
    }
}