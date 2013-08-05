<?php

namespace Ibrows\SyliusShopBundle\Controller;

use Ibrows\Bundle\WizardAnnotationBundle\Annotation\Wizard;
use Ibrows\Bundle\WizardAnnotationBundle\Annotation\AnnotationHandler as WizardHandler;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Context;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\PaymentFinishedResponse;
use Ibrows\SyliusShopBundle\Entity\Address;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Ibrows\SyliusShopBundle\Model\Cart\Payment\PaymentInterface;
use Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface;

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
        if (!$cart->isTermsAndConditions() OR $cart->getTotalWithTax() !== $cart->getAmountToPay()) {
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

        $paymentClass = $this->getParameter('ibrows_sylius_shop.payment.class');

        /* @var PaymentInterface $payment */
        $payment = new $paymentClass();
        $payment->setStrategyId($response->getStrategyId());
        $payment->setStrategyData($response->getStrategyData());
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
     * @param CartInterface $cart
     */
    protected function preAddressAction(CartInterface $cart)
    {

    }

    /**
     * @param CartInterface $cart
     * @return null|Response
     */
    protected function preSummaryAction(CartInterface $cart)
    {
        $cart->setTermsAndConditions(false);
        $this->persistCurrentCart();
        return null;
    }

    /**
     * @return InvoiceAddressInterface
     */
    protected function getInvoiceAddress()
    {
        if ($this->getCurrentCart()->getInvoiceAddress()) {
            return $this->getCurrentCart()->getInvoiceAddress();
        }
        if ($this->getUser() && $this->getUser()->getInvoiceAddress()) {
            return $this->getUser()->getInvoiceAddress();
        }

        return $this->getNewInvoiceAddress();
    }

    /**
     * @return DeliveryAddressInterface
     */
    protected function getDeliveryAddress()
    {
        if ($this->getCurrentCart()->getDeliveryAddress()) {
            return $this->getCurrentCart()->getDeliveryAddress();
        }
        if ($this->getUser() && $this->getUser()->getDeliveryAddress()) {
            return $this->getUser()->getDeliveryAddress();
        }

        return $this->getNewDeliveryAddress();
    }

    /**
     * @param Request $request
     * @param FormInterface $deliveryOptionStrategyForm
     * @param FormInterface $invoiceAddressForm
     * @param FormInterface $invoiceSameAsDeliveryForm
     * @param InvoiceAddressInterface $invoiceAddress
     * @return bool
     */
    protected function saveAddressForm(Request $request, FormInterface $deliveryOptionStrategyForm, FormInterface $invoiceAddressForm, FormInterface $invoiceSameAsDeliveryForm, InvoiceAddressInterface $invoiceAddress)
    {
        $validDeliveryOptionStrategyFormData = $this->bindDeliveryOptions($deliveryOptionStrategyForm);

        $invoiceAddressForm->bind($request);
        $invoiceSameAsDeliveryForm->bind($request);

        if ($validDeliveryOptionStrategyFormData && $invoiceAddressForm->isValid() && $invoiceSameAsDeliveryForm->isValid()) {

            $invoiceSameAsDelivery = (bool) $invoiceSameAsDeliveryForm->get('invoiceSameAsDelivery')->getData();
            $deliveryAddressForm = $this->handleDeliveryAddress($invoiceSameAsDelivery, $invoiceAddress);

            if ($deliveryAddressForm === true) {
                $cart = $this->getCurrentCart();
                $cart->setInvoiceAddress($invoiceAddress);

                if($this->getUser()){
                    $this->getUser()->setInvoiceAddress($cart->getInvoiceAddress());
                    $this->getUser()->setDeliveryAddress($cart->getDeliveryAddress());
                }

                $om = $this->getObjectManager();
                $om->persist($invoiceAddress);

                $this->persistCurrentCart();
                return true;
            }
        }
        return false;
    }

    /**
     * returns true or the form if its not valid
     *
     * @param boolean $invoiceSameAsDelivery
     * @param null $invoiceAddress
     * @throws \Exception
     * @return bool|FormInterface
     */
    protected function handleDeliveryAddress($invoiceSameAsDelivery = null, $invoiceAddress = null)
    {
        $formoptions = array(
            'data_class' => $this->getDeliveryAddressClass(),
            'validation_groups' => array(
                'sylius_wizard_address'
            )
        );

        $deliveryAddressForm = $this->createForm($this->getDeliveryAddressType(), $this->getDeliveryAddress(), $formoptions);

        //before post
        if ($invoiceSameAsDelivery === null) {
            return $deliveryAddressForm;
        }

        if($invoiceAddress == null){
            throw new \Exception('first bind invoiceaddress before use handleDeliveryAddress');
        }

        $currentcart = $this->getCurrentCart();

        //same
        if ($invoiceSameAsDelivery) {
            $currentcart->setDeliveryAddress($invoiceAddress);
            return true;
        }

        //different delivery
        if ($currentcart->getDeliveryAddress() != null && $currentcart->getInvoiceAddress() != null && ($currentcart->getDeliveryAddress()->getId() == $currentcart->getInvoiceAddress()->getId())) {
            $deliveryAddress = $this->getNewDeliveryAddress();
            $deliveryAddressForm = $this->createForm($this->getDeliveryAddressType(), $deliveryAddress,$formoptions);
        }

        $deliveryAddressForm->bind($this->getRequest());
        if (!$deliveryAddressForm->isValid()) {
            return $deliveryAddressForm;
        }

        $deliveryAddress = $deliveryAddressForm->getData();
        $em = $this->getManagerForClass($deliveryAddress);
        $em->persist($deliveryAddress);
        $currentcart->setDeliveryAddress($deliveryAddress);

        return true;
    }

    /**
     * @param FormInterface $deliveryOptionStrategyForm
     * @return boolean
     */
    protected function bindDeliveryOptions(FormInterface $deliveryOptionStrategyForm)
    {
        $deliveryOptionStrategyForm->bind($this->getRequest());

        $deliveryOptionStrategyServiceId = $deliveryOptionStrategyForm->get('strategyServiceId')->getData();
        $deliveryOptioStrategy = $this->getCurrentCartManager()->getPossibleDeliveryOptionStrategyById($deliveryOptionStrategyServiceId);

        if (!$deliveryOptioStrategy) {
            $deliveryOptionStrategyForm->addError(new FormError('cart.strategy.delivery.notfound'));
        }

        if ($deliveryOptioStrategy && $deliveryOptionStrategyForm->isValid()) {
            $cart = $this->getCurrentCart();
            $cart->setDeliveryOptionStrategyServiceId($deliveryOptioStrategy->getServiceId());
            $cart->setDeliveryOptionStrategyServiceData($deliveryOptionStrategyForm->get($deliveryOptioStrategy->getName())->getViewData());
            return true;
        }
        return false;
    }

    /**
     * @param string $action
     * @param array $data
     * @return array
     */
    protected function getViewData($action, array $data = array())
    {
        return $data;
    }

    /**
     * @return WizardHandler
     */
    protected function getWizard()
    {
        return $this->get('ibrows_wizardannotation.annotation.handler');
    }
}
