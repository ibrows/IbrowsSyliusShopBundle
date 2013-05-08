<?php

namespace Ibrows\SyliusShopBundle\Controller;
use Ibrows\SyliusShopBundle\Cart\CartManager;

use Ibrows\Bundle\WizardAnnotationBundle\Annotation\Wizard;
use Ibrows\Bundle\WizardAnnotationBundle\Annotation\AnnotationHandler as WizardHandler;

use Ibrows\SyliusShopBundle\Form\AuthType;
use Ibrows\SyliusShopBundle\Form\LoginType;
use Ibrows\SyliusShopBundle\Form\BasketType;
use Ibrows\SyliusShopBundle\Form\BasketItemType;

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
    public function paymentInstructionValidation()
    {
        $cart = $this->getCurrentCart();
        if (!$cart->getDeliveryAddress() || !$cart->getInvoiceAddress()) {
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
        if (!$cart->getPaymentInstruction()) {
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
        if (!$cart->isTermsAndConditions()) {
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
        if (!$cart->isPayed()) {
            return Wizard::REDIRECT_STEP_BACK;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getPaymentOptionsClass()
    {
        return $this->container->getParameter('ibrows_sylius_shop.paymentoptions.class');
    }

    /**
     * @return WizardHandler
     */
    protected function getWizard()
    {
        return $this->get('ibrows_wizardannotation.annotation.handler');
    }
}
