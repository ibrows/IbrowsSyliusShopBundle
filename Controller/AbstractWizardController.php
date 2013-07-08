<?php

namespace Ibrows\SyliusShopBundle\Controller;
use Ibrows\SyliusShopBundle\Cart\CartManager;

use Ibrows\Bundle\WizardAnnotationBundle\Annotation\Wizard;
use Ibrows\Bundle\WizardAnnotationBundle\Annotation\AnnotationHandler as WizardHandler;

use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Context;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\PaymentFinishedResponse;
use Ibrows\SyliusShopBundle\Entity\Payment;
use Ibrows\SyliusShopBundle\Form\AuthType;
use Ibrows\SyliusShopBundle\Form\LoginType;
use Ibrows\SyliusShopBundle\Form\BasketType;
use Ibrows\SyliusShopBundle\Form\BasketItemType;

use Ibrows\SyliusShopBundle\Form\DeliveryAddressType;
use Ibrows\SyliusShopBundle\Form\InvoiceAddressType;

use Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface;

use Ibrows\SyliusShopBundle\Entity\Address;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractWizardController extends AbstractController
{
    /**
     * @return bool|Response
     */
    public function basketValidation()
    {
        return true;
    }

    /**
     * @return bool|Response
     */
    public function authValidation()
    {
        if ($this->getCurrentCart()->isEmpty()) {
            return Wizard::REDIRECT_STEP_BACK;
        }

        return true;
    }

    /**
     * @return bool|Response
     */
    public function addressValidation()
    {
        if (!$this->getCurrentCart()->getEmail()) {
            return Wizard::REDIRECT_STEP_BACK;
        }
        return true;
    }

    /**
     * @return bool|Response
     */
    public function paymentinstructionValidation()
    {
        $cart = $this->getCurrentCart();
        if (
            !$cart->getDeliveryAddress() ||
            !$cart->getInvoiceAddress() ||
            !$cart->getDeliveryOptionStrategyServiceId()
        ) {
            return Wizard::REDIRECT_STEP_BACK;
        }
        return true;
    }
    /**
     * @return bool|Response
     */
    public function summaryValidation()
    {
        $cart = $this->getCurrentCart();
        if (!$cart->getPaymentOptionStrategyServiceId()) {
            return Wizard::REDIRECT_STEP_BACK;
        }
        return true;
    }

    /**
     * @return bool|Response
     */
    public function paymentValidation()
    {
        $cart = $this->getCurrentCart();
        if (!$cart->isTermsAndConditions()) {
            return Wizard::REDIRECT_STEP_BACK;
        }
        return true;
    }

    /**
     * @return bool|Response
     */
    public function notificationValidation()
    {
        $cart = $this->getCurrentCart();
        if (!$cart->isConfirmed()) {
            return Wizard::REDIRECT_STEP_BACK;
        }
        return true;
    }

    /**
     * @param PaymentFinishedResponse $response
     * @param Context $context
     * @return RedirectResponse
     */
    protected function handlePaymentFinishedResponse(PaymentFinishedResponse $response, Context $context)
    {
        $this->savePaymentFinishedResponse($response);

        switch($response->getStatus()){
            case $response::STATUS_OK:
                return $this->handlePaymentFinishedResponseStatusOk($response, $context);
                break;
            case $response::STATUS_ERROR:
                return $this->handlePaymentFinishedResponseStatusError($response, $context);
                break;
        }
        return $this->handlePaymentFinishedResponseStatusUnknown($response, $context);
    }

    /**
     * @param PaymentFinishedResponse $response
     */
    public function savePaymentFinishedResponse(PaymentFinishedResponse $response)
    {
        $cart = $this->getCurrentCart();
        $cartManager = $this->getCurrentCartManager();

        $payment = new Payment();
        $payment->setData($response->getData());

        $cart->addPayment($payment);

        $this->persistCart($cartManager);
    }

    /**
     * @param PaymentFinishedResponse $response
     * @param Context $context
     * @return RedirectResponse
     */
    protected function handlePaymentFinishedResponseStatusOk(PaymentFinishedResponse $response, Context $context)
    {
        $cart = $this->getCurrentCart();
        $cartManager = $this->getCurrentCartManager();

        $cart->setConfirmed();
        $cart->setPayed();

        $this->persistCart($cartManager);

        return $this->redirect($this->getWizard()->getNextStepUrl());
    }

    /**
     * @param PaymentFinishedResponse $response
     * @param Context $context
     * @return RedirectResponse
     */
    protected function handlePaymentFinishedResponseStatusError(PaymentFinishedResponse $response, Context $context)
    {
        switch($response->getErrorCode()){
            case $response::ERROR_COMPLETION:
                return $this->handlePaymentFinishedResponseStatusErrorCompletion($response, $context);
                break;
            case $response::ERROR_CONFIRMATION:
                $this->handlePaymentFinishedResponseStatusErrorConfirmation($response, $context);
                break;
            case $response::ERROR_VALIDATION:
                $this->handlePaymentFinishedResponseStatusErrorValidation($response, $context);
                break;
        }
        return $this->handlePaymentFinishedResponseStatusErrorUnknown($response, $context);
    }

    /**
     * @param PaymentFinishedResponse $response
     * @param Context $context
     * @return RedirectResponse
     */
    protected function handlePaymentFinishedResponseStatusErrorConfirmation(PaymentFinishedResponse $response, Context $context)
    {
        return $this->redirect($this->generateUrl($context->getErrorRouteName(), array(
            'paymenterror' => 'confirmation'
        )));
    }

    /**
     * @param PaymentFinishedResponse $response
     * @param Context $context
     * @return RedirectResponse
     */
    protected function handlePaymentFinishedResponseStatusErrorValidation(PaymentFinishedResponse $response, Context $context)
    {
        return $this->redirect($this->generateUrl($context->getErrorRouteName(), array(
            'paymenterror' => 'validation'
        )));
    }

    /**
     * @param PaymentFinishedResponse $response
     * @param Context $context
     * @return RedirectResponse
     */
    protected function handlePaymentFinishedResponseStatusErrorCompletion(PaymentFinishedResponse $response, Context $context)
    {
        $cart = $this->getCurrentCart();
        $cartManager = $this->getCurrentCartManager();

        $cart->setConfirmed();
        $this->persistCart($cartManager);

        return $this->redirect($this->getWizard()->getNextStepUrl());
    }

    /**
     * @param PaymentFinishedResponse $response
     * @param Context $context
     * @return RedirectResponse
     */
    protected function handlePaymentFinishedResponseStatusErrorUnknown(PaymentFinishedResponse $response, Context $context)
    {
        return $this->redirect($this->generateUrl($context->getErrorRouteName(), array(
            'paymenterror' => 'unknown'
        )));
    }

    /**
     * @param PaymentFinishedResponse $response
     * @param Context $context
     * @return RedirectResponse
     */
    protected function handlePaymentFinishedResponseStatusUnknown(PaymentFinishedResponse $response, Context $context)
    {
        return $this->redirect($this->generateUrl($context->getErrorRouteName(), array(
            'paymenterror' => 'general'
        )));
    }

    /**
     * @return WizardHandler
     */
    protected function getWizard()
    {
        return $this->get('ibrows_wizardannotation.annotation.handler');
    }
}
