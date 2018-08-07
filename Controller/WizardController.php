<?php

namespace Ibrows\SyliusShopBundle\Controller;

use Ibrows\Bundle\WizardAnnotationBundle\Annotation\Wizard;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Context;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\ErrorRedirectResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\PaymentFinishedResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\SelfRedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ibrows\SyliusShopBundle\Form\BasketType;
use Ibrows\SyliusShopBundle\Form\BasketItemType;

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
        $cart = $this->getCurrentCart();
        if (($preAction = $this->preBasketAction($cart)) instanceof Response) {
            return $preAction;
        }

        $cartManager = $this->getCurrentCartManager();
        $basketForm = $this->createForm(BasketType::class, $cart, array('basketItemType' => new BasketItemType($this->getBasketItemDataClass())));

        if ("POST" == $request->getMethod()) {
            $basketForm->handleRequest($request);

            /** @var FormInterface $basketFormItem */
            foreach ($basketForm->get('items') as $basketFormItem) {
                /** @var ClickableInterface $deleteSubmit */
                $deleteSubmit = $basketFormItem->get('delete');
                $hasDeletedItems = false;
                if ($deleteSubmit->isClicked()) {
                    $cartManager->removeItem($basketFormItem->getData());
                    $hasDeletedItems = true;
                }
                if ($hasDeletedItems) {
                    $this->persistCurrentCart();
                }
            }

            if ($basketForm->isValid()) {
                if (($postAction = $this->postBasketFormValidationAction($cart)) instanceof Response) {
                    return $postAction;
                }

                $this->persistCurrentCart();

                /** @var ClickableInterface $continueSubmit */
                $continueSubmit = $basketForm->get('continue');
                if ($continueSubmit->isClicked()) {
                    return $this->redirect($this->getWizard()->getNextStepUrl());
                }

                $this->getManagerForClass($cart)->refresh($cart);
                $basketForm = $this->createForm($this->getBasketType(), $cart);
            } else {
                if (($postAction = $this->postInvalidBasketFormValidationAction($basketForm, $cart)) instanceof Response) {
                    return $postAction;
                }
            }
        }

        return $this->getViewData(
            'basket',
            array(
                'basketForm'                    => $basketForm->createView(),
                'cart'                          => $this->getCurrentCart(),
                'deliveryOptionStrategyService' => $cartManager->getSelectedDeliveryOptionStrategyService(),
                'deliveryCosts'                 => $cartManager->getSelectedDeliveryOptionStrategyServiceCosts()
            )
        );
    }

    /**
     * @Route("/auth", name="wizard_auth")
     * @Template
     * @Wizard(name="auth", number=2, validationMethod="authValidation", visible=false)
     */
    public function authAction(Request $request)
    {
        $cart = $this->getCurrentCart();
        if (($preAction = $this->preAuthAction($cart)) instanceof Response) {
            return $preAction;
        }

        $cartManager = $this->getCurrentCartManager();

        $wizard = $this->getWizard();

        $authForm = $this->createForm(
            $this->getAuthType(),
            array('email' => $cart->getEmail()),
            array(
                'validation_groups' => array(
                    'sylius_wizard_auth'
                )
            )
        );

        $loginInformation = $this->getLoginInformation();
        $user = $loginInformation->getUser();

        if ($user && !$cart->getEmail()) {
            $cart->setEmail($user->getEmail());
            $this->persistCurrentCart();
            return $this->redirect($this->getWizard()->getNextStepUrl());
        }

        $loginForm = $this->createForm(
            $this->getLoginType(),
            array(
                '_csrf_token'   => $loginInformation->getCsrfToken(),
                '_username'     => $loginInformation->getLastUsername(),
                '_target_path'  => 'wizard_auth',
                '_failure_path' => 'wizard_auth'
            ),
            array(
                'validation_groups' => array(
                    'sylius_wizard_login'
                )
            )
        );

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

        return $this->getViewData(
            'auth',
            array(
                'cart'                 => $cart,
                'user'                 => $user,
                'authForm'             => $authForm->createView(),
                'loginForm'            => $loginForm->createView(),
                'authSubmitName'       => $authSubmitName,
                'authDeleteSubmitName' => $authDeleteSubmitName
            )
        );
    }

    /**
     * @Route("/address", name="wizard_address")
     * @Template
     * @Wizard(name="address", number=3, validationMethod="addressValidation")
     */
    public function addressAction(Request $request)
    {
        $cart = $this->getCurrentCart();
        if (($preAction = $this->preAddressAction($cart)) instanceof Response) {
            return $preAction;
        }

        $cartManager = $this->getCurrentCartManager();

        $invoiceAddress = $this->getInvoiceAddress();

        $invoiceAddressForm = $this->createForm(
            $this->getInvoiceAddressType(),
            $invoiceAddress,
            array(
                'data_class'        => get_class($invoiceAddress),
                'validation_groups' => array(
                    'sylius_wizard_address'
                )
            )
        );

        $invoiceSameAsDeliveryForm = $this->getInvoiceSameAsDeliveryForm($invoiceAddress, $this->getDeliveryAddress());

        $deliveryOptionStrategyFormData = null;
        $selectedDeliveryOptionStrategyService = null;
        $selectedDeliveryOptionStrategyServiceId = $cart->getDeliveryOptionStrategyServiceId();

        if ($selectedDeliveryOptionStrategyServiceId) {
            $selectedDeliveryOptionStrategyService = $cartManager->getPossibleDeliveryOptionStrategyById($selectedDeliveryOptionStrategyServiceId);
            if ($selectedDeliveryOptionStrategyService) {
                $deliveryOptionStrategyFormData = array(
                    'strategyServiceId'                               => $selectedDeliveryOptionStrategyServiceId,
                    $selectedDeliveryOptionStrategyService->getName() => $cart->getDeliveryOptionStrategyServiceData()
                );
            }
        }

        if ($selectedDeliveryOptionStrategyService && true === $selectedDeliveryOptionStrategyService->isSkip()) {
            return $this->redirect($this->getWizard()->getNextStepUrl());
        }

        $deliveryOptionStrategyForm = $this->createForm($this->getDeliveryOptionStrategyType($cartManager), $deliveryOptionStrategyFormData);
        $deliveryAddressForm = $this->handleDeliveryAddress();

        if ("POST" == $request->getMethod()) {
            if ($this->saveAddressForm($request, $deliveryOptionStrategyForm, $invoiceAddressForm, $invoiceSameAsDeliveryForm, $invoiceAddress, $deliveryAddressForm)) {
                return $this->redirect($this->getWizard()->getNextStepUrl());
            }
        }

        return $this->getViewData(
            'address',
            array(
                'invoiceAddressForm'                    => $invoiceAddressForm->createView(),
                'deliveryAddressForm'                   => $deliveryAddressForm->createView(),
                'deliveryOptionStrategyForm'            => $deliveryOptionStrategyForm->createView(),
                'invoiceSameAsDeliveryForm'             => $invoiceSameAsDeliveryForm->createView(),
                'selectedDeliveryOptionStrategyService' => $selectedDeliveryOptionStrategyService,
                'cart'                                  => $cart
            )
        );
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
        $selectedPaymentOptionStrategyService = null;
        if ($selectedPaymentOptionStrategyServiceId) {
            $selectedPaymentOptionStrategyService = $cartManager->getPossiblePaymentOptionStrategyById($selectedPaymentOptionStrategyServiceId);
            if ($selectedPaymentOptionStrategyService) {
                $paymentOptionStrategyFormData = array(
                    'strategyServiceId'                              => $selectedPaymentOptionStrategyServiceId,
                    $selectedPaymentOptionStrategyService->getName() => $cart->getPaymentOptionStrategyServiceData()
                );
            }
        }

        if ($selectedPaymentOptionStrategyService && true === $selectedPaymentOptionStrategyService->isSkip()) {
            return $this->redirect($this->getWizard()->getNextStepUrl());
        }

        $paymentOptionStrategyForm = $this->createForm($this->getPaymentOptionStrategyType($cartManager), $paymentOptionStrategyFormData);

        if ("POST" == $request->getMethod()) {
            $paymentOptionStrategyForm->submit($request);
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

        return $this->getViewData(
            'paymentinstruction',
            array(
                'cart'                      => $cart,
                'paymentOptionStrategyForm' => $paymentOptionStrategyForm->createView()
            )
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
        if (($preAction = $this->preSummaryAction($cart)) instanceof Response) {
            return $preAction;
        }

        $summaryForm = $this->createForm(
            $this->getSummaryType(),
            $cart,
            array(
                'validation_groups' => array(
                    'sylius_wizard_summary'
                )
            )
        );

        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $summaryForm->submit($request);
            if ($summaryForm->isValid()) {
                // Recompute strategies
                $this->persistCurrentCart();

                $this->getCurrentCartManager()->redeemVouchers();
                $this->persistCurrentCart(false);

                return $this->redirect($this->getWizard()->getNextStepUrl());
            }
        }

        $cartManager = $this->getCurrentCartManager();

        $cart->setAmountToPay($cart->getTotalWithTax());
        $this->persistCurrentCart(false);

        return $this->getViewData(
            'summary',
            array(
                'deliveryOptionStrategy'     => $cartManager->getSelectedDeliveryOptionStrategyService(),
                'paymentOptionStrategy'      => $cartManager->getSelectedPaymentOptionStrategyService(),
                'deliveryOptionStrategyData' => $cart->getDeliveryOptionStrategyServiceData(),
                'paymentOptionStrategyData'  => $cart->getPaymentOptionStrategyServiceData(),
                'summaryForm'                => $summaryForm->createView(),
                'cart'                       => $cart,
                'cartManager'                => $this->getCurrentCartManager(),
                'paymenterror'               => $request->get('paymenterror')
            )
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
        if (($preAction = $this->prePaymentAction($cart)) instanceof Response) {
            return $preAction;
        }

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
            case $response instanceof SelfRedirectResponse:
                return $this->redirect($this->generateUrl($context->getCurrentRouteName(), $response->getParameters()));
                break;
            case $response instanceof PaymentFinishedResponse:
                return $this->handlePaymentFinishedResponse($response, $context);
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
        $this->closeCurrentCart();

        return $this->getViewData(
            'notification',
            array(
                'cart' => $cart
            )
        );
    }

}