<?php

namespace Ibrows\SyliusShopBundle\Controller;

use Ibrows\Bundle\WizardAnnotationBundle\Annotation\AnnotationHandler as WizardHandler;
use Ibrows\Bundle\WizardAnnotationBundle\Annotation\Wizard;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Context;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\PaymentFinishedResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\ZeroAmountPaymentOptionCartStrategy;
use Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Payment\PaymentInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $cartManager = $this->getCurrentCartManager();

        if ($cart->getTotalWithTax() > 0 && $cartManager->getSelectedPaymentOptionStrategyService() instanceof ZeroAmountPaymentOptionCartStrategy) {
            return Wizard::REDIRECT_STEP_BACK;
        }

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
        if (!$cart->isTermsAndConditions() OR round($cart->getTotalWithTax(), 2) !== round($cart->getAmountToPay(), 2)) {
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

        $this->persistCart($cartManager, false);
    }

    /**
     * @param FormInterface $basketForm
     * @param CartInterface $cart
     * @return null|Response
     */
    protected function postInvalidBasketFormValidationAction(FormInterface $basketForm, CartInterface $cart)
    {
        return null;
    }

    /**
     * @param PaymentFinishedResponse $response
     * @param Context $context
     * @return RedirectResponse
     */
    protected function handlePaymentFinishedResponse(PaymentFinishedResponse $response, Context $context)
    {
        $this->savePaymentFinishedResponse($response);

        switch ($response->getStatus()) {
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
     * @param Context $context
     * @return RedirectResponse
     */
    protected function handlePaymentFinishedResponseStatusOk(PaymentFinishedResponse $response, Context $context)
    {
        $cart = $this->getCurrentCart();
        $cartManager = $this->getCurrentCartManager();

        $cart->setConfirmed();
        $cart->setPayed();

        $this->persistCart($cartManager, false);

        return $this->redirect($this->getWizard()->getNextStepUrl());
    }

    /**
     * @param PaymentFinishedResponse $response
     * @param Context $context
     * @return RedirectResponse
     */
    protected function handlePaymentFinishedResponseStatusError(PaymentFinishedResponse $response, Context $context)
    {
        switch ($response->getErrorCode()) {
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
        return $this->redirect(
            $this->generateUrl(
                $context->getErrorRouteName(),
                array(
                    'paymenterror' => 'confirmation'
                )
            )
        );
    }

    /**
     * @param PaymentFinishedResponse $response
     * @param Context $context
     * @return RedirectResponse
     */
    protected function handlePaymentFinishedResponseStatusErrorValidation(PaymentFinishedResponse $response, Context $context)
    {
        return $this->redirect(
            $this->generateUrl(
                $context->getErrorRouteName(),
                array(
                    'paymenterror' => 'validation'
                )
            )
        );
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
        $this->persistCart($cartManager, false);

        return $this->redirect($this->getWizard()->getNextStepUrl());
    }

    /**
     * @param PaymentFinishedResponse $response
     * @param Context $context
     * @return RedirectResponse
     */
    protected function handlePaymentFinishedResponseStatusErrorUnknown(PaymentFinishedResponse $response, Context $context)
    {
        return $this->redirect(
            $this->generateUrl(
                $context->getErrorRouteName(),
                array(
                    'paymenterror' => 'unknown'
                )
            )
        );
    }

    /**
     * @param PaymentFinishedResponse $response
     * @param Context $context
     * @return RedirectResponse
     */
    protected function handlePaymentFinishedResponseStatusUnknown(PaymentFinishedResponse $response, Context $context)
    {
        return $this->redirect(
            $this->generateUrl(
                $context->getErrorRouteName(),
                array(
                    'paymenterror' => 'general'
                )
            )
        );
    }

    /**
     * @param CartInterface $cart
     * @return Response|null
     */
    protected function preAddressAction(CartInterface $cart)
    {
        return null;
    }

    /**
     * @param CartInterface $cart
     * @return Response|null
     */
    protected function preBasketAction(CartInterface $cart)
    {
        return null;
    }

    /**
     * @param CartInterface $cart
     * @return Response|null
     */
    protected function preAuthAction(CartInterface $cart)
    {
        return null;
    }

    /**
     * @param CartInterface $cart
     * @return Response|null
     */
    protected function prePaymentAction(CartInterface $cart)
    {
        return null;
    }

    /**
     * @param CartInterface $cart
     * @return Response|null
     */
    protected function preSummaryAction(CartInterface $cart)
    {
        $cart->setTermsAndConditions(false);
        $this->persistCurrentCart();
        return null;
    }

    /**
     * @param CartInterface $cart
     * @return null|Response
     */
    protected function postBasketFormValidationAction(CartInterface $cart)
    {
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
     * @param FormInterface $invoiceSameAsDeliveryForm
     * @return bool
     */
    protected function isInvoiceSameAsDelivery(FormInterface $invoiceSameAsDeliveryForm)
    {
        return (bool)$invoiceSameAsDeliveryForm->get('invoiceSameAsDelivery')->getData();
    }

    /**
     * @param Request $request
     * @param FormInterface $deliveryOptionStrategyForm
     * @param FormInterface $invoiceAddressForm
     * @param FormInterface $invoiceSameAsDeliveryForm
     * @param InvoiceAddressInterface $invoiceAddress
     * @param FormInterface $deliveryAddressForm
     * @return bool
     */
    protected function saveAddressForm(Request $request, FormInterface $deliveryOptionStrategyForm, FormInterface &$invoiceAddressForm, FormInterface &$invoiceSameAsDeliveryForm, InvoiceAddressInterface $invoiceAddress, FormInterface &$deliveryAddressForm)
    {
        $validDeliveryOptionStrategyFormData = $this->bindDeliveryOptions($deliveryOptionStrategyForm);

        $invoiceAddressForm->bind($request);
        $invoiceSameAsDeliveryForm->bind($request);

        $invoiceSameAsDelivery = false;
        if ($invoiceSameAsDeliveryForm->isValid()) {
            $invoiceSameAsDelivery = $this->isInvoiceSameAsDelivery($invoiceSameAsDeliveryForm);
            $deliveryAddressForm = $this->handleDeliveryAddress($invoiceSameAsDelivery, $invoiceAddress);
        }

        if ($validDeliveryOptionStrategyFormData && $invoiceAddressForm->isValid() && $invoiceSameAsDeliveryForm->isValid()) {

            if ($invoiceSameAsDelivery OR $deliveryAddressForm->isValid()) {
                $cart = $this->getCurrentCart();
                $cart->setInvoiceAddress($invoiceAddress);

                if ($this->getUser()) {
                    $this->getUser()->setInvoiceAddress($cart->getInvoiceAddress());
                    $this->getUser()->setDeliveryAddress($cart->getDeliveryAddress());
                }

                $om = $this->getObjectManager();
                $om->persist($invoiceAddress);

                $this->persistCurrentCart();
                return true;
            } else {
                $invoiceSameAsDeliveryForm = $this->createForm(
                    $this->getInvoiceSameAsDeliveryType(),
                    array(
                        'invoiceSameAsDelivery' => false
                    )
                );
            }
        }

        return false;
    }

    /**
     * @param FormTypeInterface $type
     * @param DeliveryAddressInterface $deliveryAddress
     * @param array $formOptions
     * @return FormInterface
     */
    protected function createDeliveryAddressForm(FormTypeInterface $type = null, DeliveryAddressInterface $deliveryAddress = null, array $formOptions = null)
    {
        $type = $type ?: $this->getDeliveryAddressType();
        $deliveryAddress = $deliveryAddress ?: $this->getDeliveryAddress();

        $formoptions = $formOptions ?: array(
            'data_class'        => $this->getDeliveryAddressClass(),
            'validation_groups' => array(
                'sylius_wizard_address'
            )
        );

        return $this->createForm(get_class($type), $deliveryAddress, $formoptions);
    }

    /**
     * returns true or the form if its not valid
     *
     * @param boolean $invoiceSameAsDelivery
     * @param null $invoiceAddress
     * @return FormInterface
     * @throws \Exception
     */
    protected function handleDeliveryAddress($invoiceSameAsDelivery = null, $invoiceAddress = null)
    {
        $deliveryAddressForm = $this->createDeliveryAddressForm();

        //before post
        if ($invoiceSameAsDelivery === null) {
            return $deliveryAddressForm;
        }

        if ($invoiceAddress == null) {
            throw new \Exception('first bind invoiceaddress before use handleDeliveryAddress');
        }

        //same
        if ($invoiceSameAsDelivery) {
            $this->handleInvoiceIsSameAsDelivery($deliveryAddressForm, $invoiceAddress);
            return $deliveryAddressForm;
        }

        $currentcart = $this->getCurrentCart();

        //different delivery
        if ($currentcart->getDeliveryAddress() != null && $currentcart->getInvoiceAddress() != null && ($currentcart->getDeliveryAddress()->getId() == $currentcart->getInvoiceAddress()->getId())) {
            $deliveryAddressForm = $this->createDeliveryAddressForm(null, $this->getNewDeliveryAddress());
        }

        //different delivery selected but cart has no addresses and invoice and delivery are the same
        //we need to create a new delivery address, otherwise the form binding of the deliveryAddressForm would also change the invoice address
        $deliveryAddressData = $deliveryAddressForm->getData();
        if ($deliveryAddressData && $invoiceAddress && $deliveryAddressData === $invoiceAddress) {
            $deliveryAddressForm = $this->createDeliveryAddressForm(null, $this->getNewDeliveryAddress());
        }

        $deliveryAddressForm->bind($this->getRequest());
        if (!$deliveryAddressForm->isValid()) {
            return $deliveryAddressForm;
        }

        $deliveryAddress = $deliveryAddressForm->getData();
        $em = $this->getManagerForClass($deliveryAddress);
        $em->persist($deliveryAddress);
        $currentcart->setDeliveryAddress($deliveryAddress);

        return $deliveryAddressForm;
    }

    /**
     * @param FormInterface $deliveryAddressForm
     * @param InvoiceAddressInterface $invoiceAddress
     * @return bool
     */
    protected function handleInvoiceIsSameAsDelivery(FormInterface &$deliveryAddressForm, InvoiceAddressInterface $invoiceAddress)
    {
        $currentcart = $this->getCurrentCart();
        $currentcart->setDeliveryAddress($invoiceAddress);
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
