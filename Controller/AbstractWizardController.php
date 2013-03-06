<?php

namespace Ibrows\SyliusShopBundle\Controller;

use Ibrows\Bundle\WizardAnnotationBundle\Annotation\Wizard;
use Ibrows\Bundle\WizardAnnotationBundle\Annotation\AnnotationHandler as WizardHandler;

use Ibrows\SyliusShopBundle\Form\AuthType;
use Ibrows\SyliusShopBundle\Form\LoginType;

use Ibrows\SyliusShopBundle\Form\DeliveryAddressType;
use Ibrows\SyliusShopBundle\Form\InvoiceAddressType;

use Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface;

use Ibrows\SyliusShopBundle\Entity\Address;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractWizardController extends AbstractController
{
    /**
     * @return string
     */
    public function getInvoiceAddressClass()
    {
        return $this->container->getParameter('ibrows_sylius_shop.invoiceaddress.class');
    }

    /**
     * @return string
     */
    public function getDeliveryAddressClass()
    {
        return $this->container->getParameter('ibrows_sylius_shop.deliveryaddress.class');
    }

    /**
     * @return string
     */
    public function getPaymentOptionsClass()
    {
        return $this->container->getParameter('ibrows_sylius_shop.paymentoptions.class');
    }

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
        if($this->getCurrentCart()->isEmpty()){
            return Wizard::REDIRECT_STEP_BACK;
        }

        return true;
    }

    /**
     * @return bool|Response
     */
    public function addressValidation()
    {
        if(!$this->getCurrentCart()->getEmail()){
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
        if(!$cart->getDeliveryAddress() || !$cart->getInvoiceAddress()){
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
        if(!$cart->getDeliveryAddress() || !$cart->getInvoiceAddress()){
            return Wizard::REDIRECT_STEP_BACK;
        }
        return true;
    }

    /**
     * @return bool|Response
     */
    public function notificationValidation()
    {
        return true;
    }

    /**
     * @param CartInterface $cart
     */
    protected function authDelete(CartInterface $cart)
    {
        $cart->setEmail(null);
        $this->persistCart($cart);
    }

    /**
     * @param Request $request
     * @param FormInterface $authForm
     * @param CartInterface $cart
     * @param WizardHandler $wizard
     * @return RedirectResponse|void
     */
    protected function authByEmail(Request $request, FormInterface $authForm, CartInterface $cart, WizardHandler $wizard)
    {
        $authForm->bind($request);
        if($authForm->isValid()){
            $email = $authForm->get('email')->getData();
            if($this->getFOSUserManager()->findUserByEmail($email)){
                $authForm->addError(new FormError(
                    $this->translateWithPrefix("user.emailallreadyexisting", array('%email%' => $email), "validators")
                ));
            }else{
                $cart->setEmail($email);
                $this->getCartManager()->persistCart($cart);
                $this->persistCart($cart);
                return $this->redirect($wizard->getNextStepUrl());
            }
        }
    }

    /**
     * @param Request $request
     * @param FormInterface $loginForm
     */
    protected function authByUsernameAndPassword(Request $request, FormInterface $loginForm)
    {
        $loginForm->bind($request);
        if($loginForm->isValid()){
            // TODO login user with fos user and show errors if needed or redirect to nextStepUrl
        }
    }

    /**
     * @return AuthType
     */
    protected function getAuthType()
    {
        return new AuthType();
    }

    /**
     * @return LoginType
     */
    protected function getLoginType()
    {
        return new LoginType();
    }

    /**
     * @return InvoiceAddressInterface
     */
    protected function getNewInvoiceAddress()
    {
        return new Address();
    }

    /**
     * @return InvoiceAddressInterface
     */
    protected function getNewDeliveryAddress()
    {
        return new Address();
    }

    /**
     * @return InvoiceAddressType
     */
    protected function getInvoiceAddressType()
    {
        return new InvoiceAddressType();
    }

    /**
     * @return DeliveryAddressType
     */
    protected function getDeliveryAddressType()
    {
        return new DeliveryAddressType();
    }

    /**
     * @return WizardHandler
     */
    protected function getWizard()
    {
        return $this->get('ibrows_wizardannotation.annotation.handler');
    }
}