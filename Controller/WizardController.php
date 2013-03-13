<?php

namespace Ibrows\SyliusShopBundle\Controller;

use JMS\Payment\CoreBundle\Entity\FinancialTransaction;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use JMS\Payment\CoreBundle\Model\PaymentInterface;
use JMS\Payment\CoreBundle\PluginController\PluginController;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl;
use JMS\Payment\CoreBundle\PluginController\Result;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Ibrows\SyliusShopBundle\Form\PaymentOptionType;
use Ibrows\Bundle\WizardAnnotationBundle\Annotation\Wizard;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
        $basketForm = $this->createForm($this->getBasketType(), $this->getCurrentCart());
        if("POST" == $request->getMethod()) {
            $basketForm->bind($request);

            if($basketForm->isValid()) {
                $this->persistCurrentCart();
                if($request->request->get('continue')) {
                    return $this->redirect($this->getWizard()->getNextStepUrl());
                }
            }
        }

        return array(
            'basketForm' => $basketForm->createView(),
            'cart' => $this->getCurrentCart()
        );
    }

    /**
     * @Route("/auth", name="wizard_auth")
     * @Template
     * @Wizard(name="auth", number=2, validationMethod="authValidation")
     */
    public function authAction(Request $request)
    {
        $cartManager = $this->getCurrentCartManager();
        $cart        = $cartManager->getCart();
        $wizard = $this->getWizard();

        $authForm = $this
            ->createForm($this->getAuthType(), null,
            array(
                'validation_groups' => array(
                    'sylius_wizard_auth'
                )
            ));

        $loginInformation = $this->getLoginInformation();
        $user             = $loginInformation->getUser();
        if($user && !$cart->getEmail()) {
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
            ),
            array(
                'validation_groups' => array(
                    'sylius_wizard_login'
                )
            ));

        $authError = $loginInformation->getAuthenticationError();
        if($authError) {
            $loginForm->addError(new FormError($authError));
        }

        $authSubmitName       = 'auth';
        $authDeleteSubmitName = 'authDelete';
        if("POST" == $request->getMethod()) {
            if($request->request->get($authDeleteSubmitName) && !$user) {
                if(($response = $this->authDelete($cartManager)) instanceof Response) {
                    return $response;
                }
            }
            if($request->request->get($authSubmitName)) {
                if(($response = $this->authByEmail($request, $authForm, $cartManager, $wizard)) instanceof Response) {
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

    /**
     * @Route("/address", name="wizard_address")
     * @Template
     * @Wizard(name="address", number=3, validationMethod="addressValidation")
     */
    public function addressAction(Request $request)
    {
        $cart = $this->getCurrentCart();

        $invoiceaddress  = $cart->getInvoiceAddress() ? : $this->getNewInvoiceAddress();
        $deliveryAddress = $cart->getDeliveryAddress() ? : $this->getNewDeliveryAddress();

        $invoiceAddressForm = $this
            ->createForm($this->getInvoiceAddressType(), $invoiceaddress,
            array(
                'data_class' => $this->getInvoiceAddressClass(),
                'validation_groups' => array(
                    'sylius_wizard_address'
                )
            ));

        $deliveryAddressForm = $this
            ->createForm($this->getDeliveryAddressType(), $deliveryAddress,
            array(
                'data_class' => $this->getDeliveryAddressClass(),
                'validation_groups' => array(
                    'sylius_wizard_address'
                )
            ));

        if("POST" == $request->getMethod()) {
            $invoiceAddressForm->bind($request);
            $deliveryAddressForm->bind($request);
            if($invoiceAddressForm->isValid() && $deliveryAddressForm->isValid()) {
                $cart->setInvoiceAddress($invoiceaddress);
                $cart->setDeliveryAddress($deliveryAddress);
                $om = $this->getObjectManager();
                $om->persist($invoiceaddress);
                $om->persist($deliveryAddress);
                $this->persistCurrentCart();
                return $this->redirect($this->getWizard()->getNextStepUrl());
            }
        }

        return array(
            'invoiceAddressForm' => $invoiceAddressForm->createView(),
            'deliveryAddressForm' => $deliveryAddressForm->createView()
        );
    }

    /**
     * @Route("/payment_instruction", name="wizard_payment_instruction")
     * @Template
     * @Wizard(name="payment_instruction", number=4, validationMethod="paymentInstructionValidation")
     */
    public function paymentInstructionAction()
    {
        $em             = $this->getDoctrine()->getManagerForClass($this->getPaymentOptionsClass());
        $cart           = $this->getCurrentCart();
        $invoiceaddress = $cart->getInvoiceAddress();
        $ppc            = $this->get("payment.plugin_controller");

        /* @var $ppc PluginController */
        $instruction = $cart->getPaymentInstruction();
        $form = $this
            ->createForm('jms_choose_payment_method', $instruction,
            array('amount' => $cart->getTotal(), 'currency' => 'CHF', 'default_method' => null, // Optional
                'predefined_data' => array(
                    'saferpay' => array(
                        'DESCRIPTION' => sprintf('Bestellnummer: %s', $cart->getId()),
                        'ORDERID' => $cart->getId(),
                        'SUCCESSLINK' => 'http://www.test.ch/?status=success',
                        //$this->generateUrl('wizard_payment', array('status' => 'success'), true),
                        'FAILLINK' => $this->generateUrl('wizard_payment', array('status' => 'fail'), true),
                        'BACKLINK' => $this->generateUrl('wizard_payment', array(), true),
                        'FIRSTNAME' => $invoiceaddress->getFirstname(),
                        'LASTNAME' => $invoiceaddress->getLastname(),
                        'STREET' => $invoiceaddress->getStreet(),
                        'ZIP' => $invoiceaddress->getZip(),
                        'CITY' => $invoiceaddress->getCity(),
                        'COUNTRY' => $invoiceaddress->getCountry(),
                        'EMAIL' => $invoiceaddress->getEmail()
                    ),
                ),
            ));


        if('POST' === $this->getRequest()->getMethod()) {
            $form->bind($this->getRequest());
            if($form->isValid()) {
                $instruction = $form->getData();
                $ppc->createPaymentInstruction($instruction);
                $cart->setPaymentInstruction($instruction);
                $this->persistCurrentCart($cart);
            }

            return array(
                'form' => $form->createView()
            );
        }


        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/payment", name="wizard_payment")
     * @Template
     * @Wizard(name="payment", number=7, validationMethod="paymentValidation")
     */
    public function paymentAction()
    {
        $em   = $this->getDoctrine()->getManagerForClass($this->getPaymentOptionsClass());
        $cart = $this->getCurrentCart();
        if($cart->isPayed()) {
            return $this->redirect($this->getWizard()->getNextStepUrl());
        }

        $ppc = $this->get("payment.plugin_controller");

        /* @var $ppc PluginController */
        $instruction = $cart->getPaymentInstruction();
        $data        = $instruction->getExtendedData();
        $data->set('querydata', $this->getRequest()->query->all());
        $payment = null;
        /* @var $payment  \JMS\Payment\CoreBundle\Entity\Payment */
        if($instruction->getPendingTransaction() != null) {
            $pendingTransaction = $instruction->getPendingTransaction();
            $payment            = $pendingTransaction->getPayment();
        } else {
            foreach($instruction->getPayments() as $ipayment) {
                if(PaymentInterface::STATE_NEW === $ipayment->getState()) {
                    $payment = $ipayment;
                }
            }
        }

        if($payment == null) {
            $payment = $ppc->createPayment($instruction->getId(), $instruction->getAmount() - $instruction->getDepositedAmount());
        }

        if($payment->getState() == PaymentInterface::STATE_NEW || $payment->getState() == PaymentInterface::STATE_APPROVING) {
            $result = $ppc->approve($payment->getId(), $payment->getTargetAmount());
            if(Result::STATUS_PENDING === $result->getStatus()) {
                $ex = $result->getPluginException();
                if($ex instanceof ActionRequiredException) {
                    $action = $ex->getAction();
                    if($action instanceof VisitUrl) {
                        $cart->setPaymentInstruction($instruction);
                        $this->persistCurrentCart($cart);

                        return new RedirectResponse($action->getUrl());
                    }
                    throw $ex;
                }
            } else if(Result::STATUS_SUCCESS !== $result->getStatus()) {
                throw new \RuntimeException('Transaction approve was not successful: ' . $result->getReasonCode());
            }
        }

        if($payment->getState() == PaymentInterface::STATE_APPROVED || $payment->getState() == PaymentInterface::STATE_DEPOSITING) {
            $result = $ppc->deposit($payment->getId(), $payment->getTargetAmount());
            if(Result::STATUS_SUCCESS === $result->getStatus()) {
                $cart->setPayed(true);
                $cart->setPaymentInstruction($instruction);
                $this->persistCurrentCart($cart);

                return $this->redirect($this->getWizard()->getNextStepUrl());
            } else {
                throw new \RuntimeException('Transaction deposit was not successful: ' . $result->getReasonCode());
            }
        }

        throw new \Exception('Payment aborted', $payment->getState());
    }

    /**
     * @Route("/summary", name="wizard_summary")
     * @Template
     * @Wizard(name="summary", number=5, validationMethod="summaryValidation")
     */
    public function summaryAction()
    {
        return array(
            'cart' => $this->getCurrentCart()
        );
    }

    /**
     * @Route("/notification", name="wizard_notification")
     * @Template
     * @Wizard(name="notification", number=6, validationMethod="notificationValidation")
     */
    public function notificationAction()
    {
        $cart = $this->getCurrentCart();
        $this->getCurrentCartManager()->closeCart();

        return array(
            'cart' => $cart
        );
    }
}
