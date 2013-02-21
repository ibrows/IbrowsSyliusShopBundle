<?php

namespace Ibrows\SyliusShopBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

abstract class AbstractWizardValidationController extends AbstractController
{
    /**
     * @return bool|Response
     */
    public function basketValidation()
    {
        if($this->getCartManager()->getCurrentCart()->isEmpty()){
            return $this->redirect($this->generateUrl('start'));
        }
        return true;
    }

    /**
     * @return bool|Response
     */
    public function authValidation()
    {
        return true;
    }

    /**
     * @return bool|Response
     */
    public function addressValidation()
    {
        return true;
    }

    /**
     * @return bool|Response
     */
    public function paymentValidation()
    {
        return true;
    }

    /**
     * @return bool|Response
     */
    public function summaryValidation()
    {
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
     * @return WizardHandler
     */
    protected function getWizard()
    {
        return $this->get('ibrows_wizardannotation.annotation.handler');
    }
}