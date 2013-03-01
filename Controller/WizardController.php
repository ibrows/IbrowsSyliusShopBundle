<?php

namespace Ibrows\SyliusShopBundle\Controller;
use JMS\Payment\CoreBundle\Entity\FinancialTransaction;

use JMS\Payment\CoreBundle\Entity\PaymentInstruction;

use JMS\Payment\CoreBundle\Model\PaymentInterface;

use JMS\Payment\CoreBundle\PluginController\PluginController;

use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;

use JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl;

use JMS\Payment\CoreBundle\PluginController\Result;

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
    public function basketAction()
    {
        return array(
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
        $cart = $this->getCurrentCart();
        $wizard = $this->getWizard();

        $authForm = $this->createForm($this->getAuthType());
        $loginForm = $this->createForm($this->getLoginType());

        $authSubmitName = 'auth';
        $loginSubmitName = 'login';
        $authDeleteSubmitName = 'authDelete';

        if ("POST" == $request->getMethod()) {
            if ($request->request->get($authDeleteSubmitName)) {
                if (($authDelete = $this->authDelete($cart)) instanceof Response) {
                    return $authDelete;
                }
            }

            if ($request->request->get($authSubmitName)) {
                if (($authByEmail = $this->authByEmail($request, $authForm, $cart, $wizard)) instanceof Response) {
                    return $authByEmail;
                }
            }

            if ($request->request->get($loginSubmitName)) {
                $this->authByUsernameAndPassword($request, $loginSubmitName);
            }
        }

        return array(
                'cart' => $cart,
                'authForm' => $authForm->createView(),
                'loginForm' => $loginForm->createView(),
                'authSubmitName' => $authSubmitName,
                'loginSubmitName' => $loginSubmitName,
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

        $invoiceaddress = $cart->getInvoiceAddress() ? : $this->getNewInvoiceAddress();
        $deliveryAddress = $cart->getDeliveryAddress() ? : $this->getNewDeliveryAddress();

        $invoiceAddressForm = $this->createForm($this->getInvoiceAddressType(), $invoiceaddress, array(
                        'data_class' => $this->getInvoiceAddressClass()
                ));
        $deliveryAddressForm = $this->createForm($this->getDeliveryAddressType(), $deliveryAddress, array(
                        'data_class' => $this->getDeliveryAddressClass()
                ));

        if ("POST" == $request->getMethod()) {
            $invoiceAddressForm->bind($request);
            $deliveryAddressForm->bind($request);

            if ($invoiceAddressForm->isValid() && $deliveryAddressForm->isValid()) {
                $cart->setInvoiceAddress($invoiceaddress);
                $cart->setDeliveryAddress($deliveryAddress);

                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($invoiceaddress);
                $em->persist($deliveryAddress);

                $this->getCartManager()->setCurrentCart($cart);
            }
        }

        return array(
                'invoiceAddressForm' => $invoiceAddressForm->createView(),
                'deliveryAddressForm' => $deliveryAddressForm->createView()
        );
    }

    public function getInvoiceAddressClass()
    {
        return $this->container->getParameter('ibrows_sylius_shop.invoiceaddress.class');
    }
    public function getDeliveryAddressClass()
    {
        return $this->container->getParameter('ibrows_sylius_shop.deliveryaddress.class');
    }
    public function getPaymentOptionsClass()
    {
        return $this->container->getParameter('ibrows_sylius_shop.paymentoptions.class');
    }

    /**
     * @Route("/payment", name="wizard_payment")
     * @Template
     * @Wizard(name="payment", number=5, validationMethod="paymentValidation")
     */
    public function paymentAction()
    {
        $em = $this->getDoctrine()->getManagerForClass($this->getPaymentOptionsClass());
        $cart = $this->getCartManager()->getCurrentCart();
        if($cart->isPayed()){
           return $this->redirect($this->getWizard()->getNextStepUrl());
        }

        $invoiceaddress = $cart->getInvoiceAddress();
        $ppc = $this->get("payment.plugin_controller");
        /* @var $ppc PluginController   */
        $form = $this
                ->createForm('jms_choose_payment_method', null,
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

        $status = $this->getRequest()->query->get('status', false);
        if ($status == 'success') {
            $instruction = $cart->getPaymentInstruction();
            /* @var $instruction    PaymentInstruction */

            $data = $instruction->getExtendedData();
            $data->set('querydata', $this->getRequest()->query->get('DATA'));
            $data->set('signature', $this->getRequest()->query->get('SIGNATURE'));
            var_dump($status);
            $pendingTransaction = $instruction->getPendingTransaction();
            /* @var $pendingTransaction  \JMS\Payment\CoreBundle\Entity\FinancialTransaction */
            if ($pendingTransaction) {
                $payment = $pendingTransaction->getPayment();
                $pendingTransaction->setExtendedData($data);

                if ($payment->getState() == PaymentInterface::STATE_APPROVING) {
                    $result = $ppc->approve($payment->getId(), $payment->getTargetAmount());
                    if (Result::STATUS_SUCCESS !== $result->getStatus()) {
                        throw new \Exception('do approvment');
                    }
                }
            }


            foreach($instruction->getPayments() as $payment){
                if($payment->getApprovedAmount() > 0){
                    $currentpayment = $payment;
                }
            }
            $result = $ppc->deposit($currentpayment->getId(), $currentpayment->getTargetAmount());
            if (Result::STATUS_SUCCESS === $result->getStatus()) {
                $cart->setPayed(true);
                $this->getCartManager()->setCurrentCart($cart);
                return $this->redirect($this->getWizard()->getNextStepUrl());
            } else {
                throw new \RuntimeException('Transaction was not successful: ' . $result->getReasonCode());
            }
        }

        $instruction = $cart->getPaymentInstruction();
        if ($instruction == null) {
            if ('POST' === $this->getRequest()->getMethod()) {
                $form->bindRequest($this->getRequest());

                if ($form->isValid()) {
                    $instruction = $form->getData();
                    $ppc->createPaymentInstruction($instruction);
                    $cart->setPaymentInstruction($instruction);
                    $this->getCartManager()->setCurrentCart($cart);

                }
                return array(
                        'form' => $form->createView()
                );
            }


        }

        if (null === $pendingTransaction = $instruction->getPendingTransaction()) {
            $payment = $ppc->createPayment($instruction->getId(), $instruction->getAmount() - $instruction->getDepositedAmount());
        } else {
            $payment = $pendingTransaction->getPayment();
        }
        /* @var $payment  \JMS\Payment\CoreBundle\Entity\Payment */

        $result = $ppc->approve($payment->getId(), $payment->getTargetAmount());

        if (Result::STATUS_PENDING === $result->getStatus()) {
            $ex = $result->getPluginException();
            if ($ex instanceof ActionRequiredException) {
                $action = $ex->getAction();

                if ($action instanceof VisitUrl) {
                    $cart->setPaymentInstruction($instruction);
                    $this->getCartManager()->setCurrentCart($cart);
                    return new RedirectResponse($action->getUrl());
                }

                throw $ex;
            }
        } else if (Result::STATUS_SUCCESS !== $result->getStatus()) {
            throw new \RuntimeException('Transaction was not successful: ' . $result->getReasonCode());
        }

        /*
                $em = $this->getDoctrine()->getManagerForClass($this->getPaymentOptionsClass());


                $form = $this->createForm(new PaymentOptionType(), $cart,  array('data_class' => 'Ibrows\SyliusShopBundle\Entity\Cart'));

                $request = $this->getRequest();
                if("POST" == $request->getMethod()){
                    $form->bind($request);

                    if($form->isValid()){
                        $this->getCartManager()->setCurrentCart($cart);
                    }
                }

                return array('form' => $form->createView());
         */
    }

    /**
     * @Route("/summary", name="wizard_summary")
     * @Template
     * @Wizard(name="summary", number=4, validationMethod="summaryValidation")
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
        $this->getCartManager()->clearCurrentCart();
        return array();
    }
}
