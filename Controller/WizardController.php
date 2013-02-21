<?php

namespace Ibrows\SyliusShopBundle\Controller;

use Ibrows\Bundle\WizardAnnotationBundle\Annotation\Wizard;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\FormError;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/wizard")
 * @author Mike Meier
 */
class WizardController extends AbstractWizardValidationController
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

        if($cart->getEmail()){
            return $this->redirect($wizard->getNextStepUrl());
        }

        if($user = $this->getUser()){
            if(!$cart->getEmail()){
                $cart->setEmail($user->getEmail());
                $this->persistCart($cart);
            }
            return $this->redirect($wizard->getNextStepUrl());
        }

        $authForm = $this->createForm($this->getAuthType());
        $loginForm = $this->createForm($this->getLoginType());

        $authSubmitName = 'auth';
        $loginSubmitName = 'login';

        if("POST" == $request->getMethod()){
            if($request->request->get($authSubmitName)){
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

            if($request->request->get($loginSubmitName)){
                $loginForm->bind($request);
                if($loginForm->isValid()){

                }
            }
        }

        return array(
            'authForm' => $authForm->createView(),
            'loginForm' => $loginForm->createView(),

            'authSubmitName' => $authSubmitName,
            'loginSubmitName' => $loginSubmitName
        );
    }

    /**
     * @Route("/address", name="wizard_address")
     * @Template
     * @Wizard(name="address", number=3, validationMethod="addressValidation")
     */
    public function addressAction()
    {
        return array();
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
