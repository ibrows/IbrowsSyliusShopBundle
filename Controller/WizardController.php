<?php

namespace Ibrows\SyliusShopBundle\Controller;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Context;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\ErrorRedirectResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\PaymentFinishedResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\SelfRedirectResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\FormError;
use Ibrows\Bundle\WizardAnnotationBundle\Annotation\Wizard;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;

/**
 * @Route("/wizard")
 * @author Mike Meier
 */
class WizardController extends AbstractWizardController
{
    /**
     * @Route("/basket", name="wizard_basket")
     * @Template
     * @Wizard(name="basket", number=1, validationMethod="basketValidation")
     */
    public function basketAction(Request $request)
    {
        $continueSubmitName = 'continue';
        $deleteSubmitName = 'delete';

        $cart = $this->getCurrentCart();
        $cartManager = $this->getCurrentCartManager();
        $basketForm = $this->createForm($this->getBasketType(), $cart);

        if ("POST" == $request->getMethod()) {
            if (($deleteItems = $request->request->get($deleteSubmitName)) && is_array($deleteItems)) {
                foreach ($deleteItems as $itemId => $formName) {
                    if ($item = $cart->getItemById($itemId)) {
                        $cartManager->removeItem($item);
                    }
                }
                $this->persistCurrentCart();
                $basketForm = $this->createForm($this->getBasketType(), $cart);
            }

            $basketForm->bind($request);
            if ($basketForm->isValid()) {
                $this->persistCurrentCart();
                $basketForm = $this->createForm($this->getBasketType(), $cart);
                if ($request->request->get($continueSubmitName)) {
                    return $this->redirect($this->getWizard()->getNextStepUrl());
                }
            }
        }

        return array(
                'deleteSubmitName' => $deleteSubmitName,
                'continueSubmitName' => $continueSubmitName,
                'basketForm' => $basketForm->createView(),
                'cart' => $this->getCurrentCart(),
                'deliveryOptionStrategyService' => $cartManager->getSelectedDeliveryOptionStrategyService(),
                'deliveryCosts' => $cartManager->getSelectedDeliveryOptionStrategyServiceCosts()
        );
    }

    /**
     * @Route("/auth", name="wizard_auth")
     * @Template
     * @Wizard(name="auth", number=2, validationMethod="authValidation", visible=false)
     */
    public function authAction(Request $request)
    {
        $cartManager = $this->getCurrentCartManager();
        $cart = $cartManager->getCart();
        $wizard = $this->getWizard();

        $authForm = $this->createForm($this->getAuthType(), null, array(
                        'validation_groups' => array(
                                'sylius_wizard_auth'
                        )
                ));

        $loginInformation = $this->getLoginInformation();
        $user = $loginInformation->getUser();

        if ($user && !$cart->getEmail()) {
            $cart->setEmail($user->getEmail());
            $this->persistCurrentCart();
            return $this->redirect($this->getWizard()->getNextStepUrl());
        }

        $loginForm = $this
                ->createForm($this->getLoginType(),
                        array(
                                '_csrf_token' => $loginInformation->getCsrfToken(),
                                '_username' => $loginInformation->getLastUsername(),
                                '_target_path' => 'wizard_auth',
                                '_failure_path' => 'wizard_auth'
                        ), array(
                                'validation_groups' => array(
                                        'sylius_wizard_login'
                                )
                        ));

        $authError = $loginInformation->getAuthenticationError();
        if ($authError) {
            $loginForm->addError(new FormError($authError));
        }

        $authSubmitName = 'auth';
        $authDeleteSubmitName = 'authDelete';

        if ("POST" == $request->getMethod()) {
            if ($request->request->get($authDeleteSubmitName) && !$user) {
                if (($response = $this->authDelete($cartManager)) instanceof Response) {
                    return $response;
                }
            }
            if ($request->request->get($authSubmitName)) {
                if (($response = $this->authByEmail($request, $authForm, $cartManager, $wizard)) instanceof Response) {
                    return $response;
                }
            }
        }

        return array(
                'cart' => $cart,
                'user' => $user,
                'authForm' => $authForm->createView(),
                'loginForm' => $loginForm->createView(),
                'authSubmitName' => $authSubmitName,
                'authDeleteSubmitName' => $authDeleteSubmitName
        );
    }


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
     * @Route("/address", name="wizard_address")
     * @Template
     * @Wizard(name="address", number=3, validationMethod="addressValidation")
     */
    public function addressAction(Request $request)
    {
        $cart = $this->getCurrentCart();
        $cartManager = $this->getCurrentCartManager();

        $invoiceAddress = $this->getInvoiceAddress();

        $invoiceAddressForm = $this
                ->createForm($this->getInvoiceAddressType(), $invoiceAddress,
                        array(
                                'data_class' => get_class($invoiceAddress),
                                'validation_groups' => array(
                                        'sylius_wizard_address'
                                )
                        ));


        $invoiceSameAsDeliveryForm = $this
                ->createForm($this->getInvoiceSameAsDeliveryType(), array(
                                'invoiceSameAsDelivery' => $invoiceAddress->compare($this->getDeliveryAddress())
                        ), array(
                                'attr' => array(
                                        'data-invoice-same-as-delivery' => true
                                )
                        ));

        $deliveryOptionStrategyFormData = null;
        $selectedDeliveryOptionStrategyService = null;
        $selectedDeliveryOptionStrategyServiceId = $cart->getDeliveryOptionStrategyServiceId();

        if ($selectedDeliveryOptionStrategyServiceId) {
            $selectedDeliveryOptionStrategyService = $cartManager->getPossibleDeliveryOptionStrategyById($selectedDeliveryOptionStrategyServiceId);
            if ($selectedDeliveryOptionStrategyService) {
                $deliveryOptionStrategyFormData = array(
                        'strategyServiceId' => $selectedDeliveryOptionStrategyServiceId,
                        $selectedDeliveryOptionStrategyService->getName() => $cart->getDeliveryOptionStrategyServiceData()
                );
            }
        }

        $deliveryOptionStrategyForm = $this->createForm($this->getDeliveryOptionStrategyType($cartManager), $deliveryOptionStrategyFormData);
        $deliveryAddressForm = $this->handleDeliveryAddress(null);

        if ("POST" == $request->getMethod()) {
            $validDeliveryOptionStrategyFormData = $this->bindDeliveryOptions($deliveryOptionStrategyForm);

            $invoiceAddressForm->bind($request);
            $invoiceSameAsDeliveryForm->bind($request);

            if ($validDeliveryOptionStrategyFormData && $invoiceAddressForm->isValid() && $invoiceSameAsDeliveryForm->isValid()) {

                $invoiceSameAsDelivery = (bool) $invoiceSameAsDeliveryForm->get('invoiceSameAsDelivery')->getData();
                $deliveryAddressForm = $this->handleDeliveryAddress($invoiceSameAsDelivery,$invoiceAddress);
                if ($deliveryAddressForm === true) {
                    $cart->setInvoiceAddress($invoiceAddress);

                    if($this->getUser()){
                        $this->getUser()->setInvoiceAddress($cart->getInvoiceAddress());
                        $this->getUser()->setDeliveryAddress($cart->getDeliveryAddress());
                    }

                    $om = $this->getObjectManager();
                    $om->persist($invoiceAddress);

                    $this->persistCurrentCart();

                    return $this->redirect($this->getWizard()->getNextStepUrl());
                }
            }
            die('z');
        }

        return array(
                'invoiceAddressForm' => $invoiceAddressForm->createView(),
                'deliveryAddressForm' => $deliveryAddressForm->createView(),
                'deliveryOptionStrategyForm' => $deliveryOptionStrategyForm->createView(),
                'invoiceSameAsDeliveryForm' => $invoiceSameAsDeliveryForm->createView(),
                'selectedDeliveryOptionStrategyService' => $selectedDeliveryOptionStrategyService,
                'cart' => $cart
        );
    }


    /**
     * returns true or the form if its not valid
     *
     * @param boolean $invoiceSameAsDelivery
     * @return \Symfony\Component\Form\Form
     */
    protected function handleDeliveryAddress($invoiceSameAsDelivery = null, $invoiceAddress = null)
    {
        $formoptions = array(
                                'data_class' => $this->getDeliveryAddressClass(),
                                'validation_groups' => array(
                                        'sylius_wizard_address'
                                )
                        );
        $deliveryAddressForm = $this->createForm($this->getDeliveryAddressType(), $this->getDeliveryAddress(),$formoptions);
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

        $currentcart->setDeliveryAddress($deliveryAddressForm->getData());
        $this->getObjectManager()->persist($deliveryAddressForm->getData());
        return true;
    }

    /**
     * @param Form $deliveryOptionStrategyForm
     * @return boolean
     */
    protected function bindDeliveryOptions(Form $deliveryOptionStrategyForm)
    {

        $deliveryOptionStrategyServiceId = $deliveryOptionStrategyForm->get('strategyServiceId')->getData();
        $deliveryOptioStrategy = $this->getCurrentCartManager()->getPossibleDeliveryOptionStrategyById($deliveryOptionStrategyServiceId);

        if (!$deliveryOptioStrategy) {
            $deliveryOptionStrategyForm->addError(new FormError('cart.strategy.delivery.notfound'));
        }
        $deliveryOptionStrategyForm->bind($this->getRequest());

        if ($deliveryOptioStrategy && $deliveryOptionStrategyForm->isValid()) {
            die();
            $cart = $this->getCurrentCart();
            $cart->setDeliveryOptionStrategyServiceId($deliveryOptioStrategy->getServiceId());
            $cart->setDeliveryOptionStrategyServiceData($deliveryOptionStrategyForm->get($deliveryOptioStrategy->getName())->getViewData());
            return true;
        }
        return false;
    }

    /**
     * @Route("/payment_instruction", name="wizard_payment_instruction")
     * @Template
     * @Wizard(name="payment_instruction", number=4, validationMethod="paymentinstructionValidation")
     */
    public function paymentinstructionAction(Request $request)
    {
        $cartManager = $this->getCurrentCartManager();
        $cart = $cartManager->getCart();

        $paymentOptionStrategyFormData = null;
        $selectedPaymentOptionStrategyServiceId = $cart->getPaymentOptionStrategyServiceId();
        if ($selectedPaymentOptionStrategyServiceId) {
            $selectedPaymentOptionStrategyService = $cartManager->getPossiblePaymentOptionStrategyById($selectedPaymentOptionStrategyServiceId);
            if ($selectedPaymentOptionStrategyService) {
                $paymentOptionStrategyFormData = array(
                        'strategyServiceId' => $selectedPaymentOptionStrategyServiceId,
                        $selectedPaymentOptionStrategyService->getName() => $cart->getPaymentOptionStrategyServiceData()
                );
            }
        }

        $paymentOptionStrategyForm = $this->createForm($this->getPaymentOptionStrategyType($cartManager), $paymentOptionStrategyFormData);

        if ("POST" == $request->getMethod()) {
            $paymentOptionStrategyForm->bind($request);
            if ($paymentOptionStrategyForm->isValid()) {
                $paymentOptionStrategyServiceId = $paymentOptionStrategyForm->get('strategyServiceId')->getData();
                $paymentOptionStrategy = $cartManager->getPossiblePaymentOptionStrategyById($paymentOptionStrategyServiceId);

                if (!$paymentOptionStrategy) {
                    $paymentOptionStrategyForm->addError(new FormError('cart.strategy.payment.notfound'));
                } else {
                    $cart->setPaymentOptionStrategyServiceId($paymentOptionStrategy->getServiceId());
                    $cart->setPaymentOptionStrategyServiceData($paymentOptionStrategyForm->get($paymentOptionStrategy->getName())->getViewData());
                    $this->persistCurrentCart();
                    return $this->redirect($this->getWizard()->getNextStepUrl());
                }
            }
        }

        return array(
                'cart' => $cart,
                'paymentOptionStrategyForm' => $paymentOptionStrategyForm->createView()
        );
    }

    /**
     * @Route("/summary", name="wizard_summary")
     * @Template
     * @Wizard(name="summary", number=5, validationMethod="summaryValidation")
     */
    public function summaryAction()
    {
        $cart = $this->getCurrentCart();
        $cart->setTermsAndConditions(false);
        $this->persistCurrentCart();

        $summaryForm = $this->createForm($this->getSummaryType(), $cart, array(
                        'validation_groups' => array(
                                'sylius_wizard_summary'
                        )
                ));

        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $summaryForm->bind($request);
            if ($summaryForm->isValid()) {
                $this->persistCurrentCart();
                return $this->redirect($this->getWizard()->getNextStepUrl());
            }
        }

        $cartManager = $this->getCurrentCartManager();

        return array(
                'deliveryOptionStrategy' => $cartManager->getSelectedDeliveryOptionStrategyService(),
                'paymentOptionStrategy' => $cartManager->getSelectedPaymentOptionStrategyService(),
                'deliveryOptionStrategyData' => $cart->getDeliveryOptionStrategyServiceData(),
                'paymentOptionStrategyData' => $cart->getPaymentOptionStrategyServiceData(),
                'summaryForm' => $summaryForm->createView(),
                'cart' => $cart,
                'cartManager' => $this->getCurrentCartManager(),
                'paymenterror' => $request->get('paymenterror')
        );
    }

    /**
     * @Route("/payment", name="wizard_payment")
     * @Template
     * @Wizard(name="payment", number=6, validationMethod="paymentValidation")
     */
    public function paymentAction(Request $request)
    {
        $cart = $this->getCurrentCart();
        if ($cart->isPayed()) {
            return $this->redirect($this->getWizard()->getNextStepUrl());
        }

        $context = new Context($request, 'wizard_payment', 'wizard_summary');

        $cartManager = $this->getCurrentCartManager();
        $paymentOptionStrategyService = $cartManager->getSelectedPaymentOptionStrategyService();
        $response = $paymentOptionStrategyService->pay($context, $cart, $cartManager);

        switch (true) {
        case $response instanceof RedirectResponse:
            return $response;
            break;
        case $response instanceof ErrorRedirectResponse:
            return new RedirectResponse($this->generateUrl($context->getErrorRouteName(), $response->getParameters()));
            break;
        case $response instanceof PaymentFinishedResponse:
            if ($response->getStatus() == $response::STATUS_OK) {
                $cart->setConfirmed();
                $cart->setPayed();
                $this->persistCart($cartManager);
                return $this->redirect($this->getWizard()->getNextStepUrl());
            }

            if ($response->getStatus() == $response::STATUS_ERROR) {
                switch ($response->getErrorCode()) {
                case $response::ERROR_COMPLETION:
                    $cart->setConfirmed();
                    $this->persistCart($cartManager);
                    return $this->redirect($this->getWizard()->getNextStepUrl());
                    break;
                case $response::ERROR_CONFIRMATION:
                    return $this->redirect($this->generateUrl($context->getErrorRouteName(), array(
                                            'paymenterror' => 'confirmation'
                                    )));
                    break;
                case $response::ERROR_VALIDATION:
                    return $this->redirect($this->generateUrl($context->getErrorRouteName(), array(
                                            'paymenterror' => 'validation'
                                    )));
                    break;
                }
            }

            return $this->redirect($this->generateUrl($context->getErrorRouteName(), array(
                                    'paymenterror' => 'general'
                            )));
            break;
        case $response instanceof SelfRedirectResponse:
            return $this->redirect($this->generateUrl($context->getCurrentRouteName(), $response->getParameters()));
            break;
        }

        throw $this->createNotFoundException("ResponseType for PaymentGateway not found");
    }

    /**
     * @Route("/notification", name="wizard_notification")
     * @Template
     * @Wizard(name="notification", number=7, validationMethod="notificationValidation")
     */
    public function notificationAction()
    {
        $cart = $this->getCurrentCart();

        // Close card in notification because its not available any more after that step
        $this->getCurrentCartManager()->closeCart();

        return array(
                'cart' => $cart
        );
    }
}
