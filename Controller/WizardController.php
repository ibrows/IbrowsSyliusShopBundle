<?php

namespace Ibrows\SyliusShopBundle\Controller;

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

        if("POST" == $request->getMethod()){
            if($request->request->get($authDeleteSubmitName)){
                if(($authDelete = $this->authDelete($cart)) instanceof Response){
                    return $authDelete;
                }
            }

            if($request->request->get($authSubmitName)){
                if(($authByEmail = $this->authByEmail($request, $authForm, $cart, $wizard)) instanceof Response){
                    return $authByEmail;
                }
            }

            if($request->request->get($loginSubmitName)){
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

        $invoiceAddressForm = $this->createForm($this->getInvoiceAddressType());
        $deliveryAddressForm = $this->createForm($this->getDeliveryAddressType());

        $invoiceAddressForm->setData($cart->getInvoiceAddress()?:$this->getNewInvoiceAddress());
        $deliveryAddressForm->setData($cart->getDeliveryAddress()?:$this->getNewDeliveryAddress());

        if("POST" == $request->getMethod()){
            $invoiceAddressForm->bind($request);
            $deliveryAddressForm->bind($request);

            if($invoiceAddressForm->isValid() && $deliveryAddressForm->isValid()){
                $cart->setInvoiceAddress($invoiceAddressForm->getData());
                $cart->setDeliveryAddress($deliveryAddressForm->getData());
            }
        }

        return array(
            'invoiceAddressForm' => $invoiceAddressForm->createView(),
            'deliveryAddressForm' => $deliveryAddressForm->createView()
        );
    }

    /**
     * @Route("/payment", name="wizard_payment")
     * @Template
     * @Wizard(name="payment", number=4, validationMethod="paymentValidation")
     */
    public function paymentAction()
    {
        return array();
    }

    /**
     * @Route("/summary", name="wizard_summary")
     * @Template
     * @Wizard(name="summary", number=5, validationMethod="summaryValidation")
     */
    public function summaryAction()
    {
        return array();
    }

    /**
     * @Route("/notification", name="wizard_notification")
     * @Template
     * @Wizard(name="notification", number=6, validationMethod="notificationValidation")
     */
    public function notificationAction()
    {
        return array();
    }
}
