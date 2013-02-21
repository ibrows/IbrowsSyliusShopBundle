<?php

namespace Ibrows\SyliusShopBundle\Controller;

use Ibrows\Bundle\WizardAnnotationBundle\Annotation\Wizard;
use Ibrows\Bundle\WizardAnnotationBundle\Annotation\AnnotationHandler as WizardHandler;

use Symfony\Component\HttpFoundation\Response;

abstract class AbstractWizardValidationController extends AbstractController
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