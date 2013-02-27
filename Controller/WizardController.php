<?php

namespace Ibrows\SyliusShopBundle\Controller;

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

        $invoiceaddress = $cart->getInvoiceAddress()?:$this->getNewInvoiceAddress();
        $deliveryAddress = $cart->getDeliveryAddress()?:$this->getNewDeliveryAddress();

        $invoiceAddressForm = $this->createForm($this->getInvoiceAddressType(), $invoiceaddress, array('data_class' => $this->getInvoiceAddressClass()));
        $deliveryAddressForm = $this->createForm($this->getDeliveryAddressType(), $deliveryAddress, array('data_class' => $this->getDeliveryAddressClass()));


        if("POST" == $request->getMethod()){
            $invoiceAddressForm->bind($request);
            $deliveryAddressForm->bind($request);

            if($invoiceAddressForm->isValid() && $deliveryAddressForm->isValid()){
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

    public function getInvoiceAddressClass(){
        return $this->container->getParameter('ibrows_sylius_shop.invoiceaddress.class');
    }
    public function getDeliveryAddressClass(){
        return $this->container->getParameter('ibrows_sylius_shop.deliveryaddress.class');
    }
    public function getPaymentOptionsClass(){
        return $this->container->getParameter('ibrows_sylius_shop.paymentoptions.class');
    }

    /**
     * @Route("/payment", name="wizard_payment")
     * @Template
     * @Wizard(name="payment", number=4, validationMethod="paymentValidation")
     */
    public function paymentAction()
    {
        $em = $this->getDoctrine()->getEntityManagerForClass($this->getPaymentOptionsClass());

        $cart = $this->getCartManager()->getCurrentCart();
        $form = $this->createForm(new PaymentOptionType(), $cart,  array('data_class' => 'Ibrows\SyliusShopBundle\Entity\Cart'));

        $request = $this->getRequest();
        if("POST" == $request->getMethod()){
            $form->bind($request);

            if($form->isValid()){
                $this->getCartManager()->setCurrentCart($cart);
            }
        }

        return array('form' => $form->createView());
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
        return array();
    }
}
