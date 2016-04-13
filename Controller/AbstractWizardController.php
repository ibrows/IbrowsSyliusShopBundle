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
     * @param Request $request
     * @return bool|Response
     */
    public function basketValidation(Request $request)
    {
        return true;
    }

    /**
     * @param Request $request
     * @return bool|Response
     */
    public function authValidation(Request $request)
    {
        if ($this->getCurrentCart()->isEmpty()) {
            return Wizard::REDIRECT_STEP_BACK;
        }

        return true;
    }

    /**
     * @param Request $request
     * @return bool|Response
     */
    public function addressValidation(Request $request)
    {
        if (!$this->getCurrentCart()->getEmail()) {
            return Wizard::REDIRECT_STEP_BACK;
        }

        return true;
    }

    /**
     * @param Request $request
     * @return bool|Response
     */
    public function paymentinstructionValidation(Request $request)
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
     * @param Request $request
     * @return bool|Response
     */
    public function summaryValidation(Request $request)
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
     * @param Request $request
     * @return bool|Response
     */
    public function paymentValidation(Request $request)
    {
        $cart = $this->getCurrentCart();
        if (!$cart->isTermsAndConditions() or round($cart->getTotalWithTax(), 2) !== round($cart->getAmountToPay(), 2)) {
            return Wizard::REDIRECT_STEP_BACK;
        }

        return true;
    }

    /**
     * @param Request $request
     * @return bool|Response
     */
    public function notificationValidation(Request $request)
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
     * @param Request $request
     * @param FormInterface $basketForm
     * @param CartInterface $cart
     * @return null|Response
     */
    protected function postInvalidBasketFormValidationAction(Request $request, FormInterface $basketForm, CartInterface $cart)
    {
        return;
    }

    /**
     * @param PaymentFinishedResponse $response
     * @param Context $context
     *
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
     *
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
     *
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
     *
     * @return RedirectResponse
     */
    protected function handlePaymentFinishedResponseStatusErrorConfirmation(PaymentFinishedResponse $response, Context $context)
    {
        return $this->redirect(
            $this->generateUrl(
                $context->getErrorRouteName(),
                array(
                    'paymenterror' => 'confirmation',
                )
            )
        );
    }

    /**
     * @param PaymentFinishedResponse $response
     * @param Context $context
     *
     * @return RedirectResponse
     */
    protected function handlePaymentFinishedResponseStatusErrorValidation(PaymentFinishedResponse $response, Context $context)
    {
        return $this->redirect(
            $this->generateUrl(
                $context->getErrorRouteName(),
                array(
                    'paymenterror' => 'validation',
                )
            )
        );
    }

    /**
     * @param PaymentFinishedResponse $response
     * @param Context $context
     *
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
     *
     * @return RedirectResponse
     */
    protected function handlePaymentFinishedResponseStatusErrorUnknown(PaymentFinishedResponse $response, Context $context)
    {
        return $this->redirect(
            $this->generateUrl(
                $context->getErrorRouteName(),
                array(
                    'paymenterror' => 'unknown',
                )
            )
        );
    }

    /**
     * @param PaymentFinishedResponse $response
     * @param Context $context
     *
     * @return RedirectResponse
     */
    protected function handlePaymentFinishedResponseStatusUnknown(PaymentFinishedResponse $response, Context $context)
    {
        return $this->redirect(
            $this->generateUrl(
                $context->getErrorRouteName(),
                array(
                    'paymenterror' => 'general',
                )
            )
        );
    }

    /**
     * @param Request $request
     * @param CartInterface $cart
     * @return null|Response
     */
    protected function preAddressAction(Request $request, CartInterface $cart)
    {
        return;
    }

    /**
     * @param Request $request
     * @param CartInterface $cart
     * @return null|Response
     */
    protected function preBasketAction(Request $request, CartInterface $cart)
    {
        return;
    }

    /**
     * @param Request $request
     * @param CartInterface $cart
     * @return null|Response
     */
    protected function preAuthAction(Request $request, CartInterface $cart)
    {
        return;
    }

    /**
     * @param Request $request
     * @param CartInterface $cart
     * @return null|Response
     */
    protected function prePaymentAction(Request $request, CartInterface $cart)
    {
        return;
    }

    /**
     * @param Request $request
     * @param CartInterface $cart
     * @return null|Response
     */
    protected function preSummaryAction(Request $request, CartInterface $cart)
    {
        $cart->setTermsAndConditions(false);
        $this->persistCurrentCart();

        return;
    }

    /**
     * @param Request $request
     * @param CartInterface $cart
     * @return null|Response
     */
    protected function postBasketFormValidationAction(Request $request, CartInterface $cart)
    {
        return;
    }

    /**
     * @return InvoiceAddressInterface
     */
    protected function getInvoiceAddress()
    {
        if ($invoiceAddress = $this->getCurrentCart()->getInvoiceAddress()) {
            return $invoiceAddress;
        }

        if (($user = $this->getUser()) && ($invoiceAddress = $user->getInvoiceAddress())) {
            return $invoiceAddress;
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
     *
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
     *
     * @return bool
     */
    protected function saveAddressForm(Request $request, FormInterface $deliveryOptionStrategyForm, FormInterface &$invoiceAddressForm, FormInterface &$invoiceSameAsDeliveryForm, InvoiceAddressInterface $invoiceAddress, FormInterface &$deliveryAddressForm)
    {
        $validDeliveryOptionStrategyFormData = $this->bindDeliveryOptions($request, $deliveryOptionStrategyForm);

        $invoiceAddressForm->handleRequest($request);
        $invoiceSameAsDeliveryForm->handleRequest($request);

        $invoiceSameAsDelivery = false;
        if ($invoiceSameAsDeliveryForm->isValid()) {
            $invoiceSameAsDelivery = $this->isInvoiceSameAsDelivery($invoiceSameAsDeliveryForm);
            $deliveryAddressForm = $this->handleDeliveryAddress($request, $invoiceSameAsDelivery, $invoiceAddress);
        }

        if (
            $validDeliveryOptionStrategyFormData &&
            ($invoiceAddressForm->isValid() || !$invoiceAddressForm->isSubmitted()) &&
            $invoiceSameAsDeliveryForm->isValid()
        ) {
            if ($invoiceSameAsDelivery or $deliveryAddressForm->isValid()) {
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
                        'invoiceSameAsDelivery' => false,
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
     *
     * @return FormInterface
     */
    protected function createDeliveryAddressForm(FormTypeInterface $type = null, DeliveryAddressInterface $deliveryAddress = null, array $formOptions = null)
    {
        $type = $type ?: $this->getDeliveryAddressType();
        $deliveryAddress = $deliveryAddress ?: $this->getDeliveryAddress();

        $formoptions = $formOptions ?: array(
            'data_class'        => $this->getDeliveryAddressClass(),
            'validation_groups' => array(
                'sylius_wizard_address',
            ),
        );

        return $this->createForm($type, $deliveryAddress, $formoptions);
    }

    /**
     * returns true or the form if its not valid.
     *
     * @param Request $request
     * @param bool $invoiceSameAsDelivery
     * @param null $invoiceAddress
     * @return FormInterface
     * @throws \Exception
     */
    protected function handleDeliveryAddress(Request $request, $invoiceSameAsDelivery = null, $invoiceAddress = null)
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
            $this->handleInvoiceIsSameAsDelivery($request, $deliveryAddressForm, $invoiceAddress);
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

        $deliveryAddressForm->handleRequest($request);
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
     * @param Request $request
     * @param FormInterface $deliveryAddressForm
     * @param InvoiceAddressInterface $invoiceAddress
     * @return bool
     */
    protected function handleInvoiceIsSameAsDelivery(Request $request, FormInterface &$deliveryAddressForm, InvoiceAddressInterface $invoiceAddress)
    {
        $currentcart = $this->getCurrentCart();
        $currentcart->setDeliveryAddress($invoiceAddress);

        return true;
    }

    /**
     * @param Request $request
     * @param FormInterface $deliveryOptionStrategyForm
     * @return bool
     */
    protected function bindDeliveryOptions(Request $request, FormInterface $deliveryOptionStrategyForm)
    {
        $deliveryOptionStrategyForm->handleRequest($request);

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
     * @param Request $request
     * @param string $action
     * @param array $data
     * @return array
     */
    protected function getViewData(Request $request, $action, array $data = array())
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
