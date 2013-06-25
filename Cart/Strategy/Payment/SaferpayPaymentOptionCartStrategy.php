<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Payment;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\PaymentFinishedResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\SelfRedirectResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\ErrorRedirectResponse;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Payment\Bundle\SaferpayBundle\PayInitParameter\PayInitParameterFactory;
use Payment\Saferpay\Data\PayConfirmParameterInterface;
use Payment\Saferpay\Data\PayInitParameterInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Payment\Saferpay\Saferpay;
use Payment\Saferpay\Data\PayInitParameterWithDataInterface;

class SaferpayPaymentOptionCartStrategy extends AbstractPaymentOptionCartStrategy
{
    /**
     * @var Saferpay
     */
    protected $saferpay;

    /**
     * @var PayInitParameterFactory
     */
    protected $payInitParameterFactory;

    /**
     * @var array
     */
    protected $paymentMethods;

    /**
     * @var bool
     */
    protected $doCompletePayment;

    /**
     * @param Saferpay $saferpay
     * @param PayInitParameterFactory $payInitParameterFactory
     * @param array $paymentMethods
     * @param bool $doCompletePayment
     */
    public function __construct(Saferpay $saferpay, PayInitParameterFactory $payInitParameterFactory, array $paymentMethods, $doCompletePayment = true)
    {
        $this->saferpay = $saferpay;
        $this->payInitParameterFactory = $payInitParameterFactory;
        $this->paymentMethods = $paymentMethods;
        $this->setParentVisible(false);
        $this->setDoCompletePayment($doCompletePayment);
    }

    /**
     * @return array
     */
    public function getPaymentMethods()
    {
        return $this->paymentMethods;
    }

    /**
     * @param array $paymentMethods
     * @return SaferpayPaymentOptionCartStrategy
     */
    public function setPaymentMethods(array $paymentMethods)
    {
        $this->paymentMethods = $paymentMethods;
        return $this;
    }

    /**
     * @return Saferpay
     */
    protected function getSaferpay()
    {
        return $this->saferpay;
    }

    /**
     * @param Saferpay $saferpay
     * @return SaferpayPaymentOptionCartStrategy
     */
    protected function setSaferpay(Saferpay $saferpay)
    {
        $this->saferpay = $saferpay;
        return $this;
    }

    /**
     * @return boolean
     */
    public function doCompletePayment()
    {
        return $this->doCompletePayment;
    }

    /**
     * @param boolean $doCompletePayment
     * @return SaferpayPaymentOptionCartStrategy
     */
    public function setDoCompletePayment($doCompletePayment)
    {
        $this->doCompletePayment = $doCompletePayment;
        return $this;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return bool
     */
    public function isPossible(CartInterface $cart, CartManager $cartManager)
    {
        return true;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return AdditionalCartItemInterface[]
     */
    public function compute(CartInterface $cart, CartManager $cartManager)
    {
        $data = $cart->getPaymentOptionStrategyServiceData();
        if(!isset($data['method'])){
            $this->removeStrategy($cart);
            return array();
        }
        return array();
    }

    /**
     * @param Context $context
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return RedirectResponse|PaymentFinishedResponse|ErrorRedirectResponse|SelfRedirectResponse
     */
    public function pay(Context $context, CartInterface $cart, CartManager $cartManager)
    {
        $request = $context->getRequest();
        $saferpay = $this->getSaferpay();
        $payInitParameter = $this->getPayInitParameter($context, $cart);

        switch($request->query->get('status')){
            case PaymentFinishedResponse::STATUS_OK:
                try {
                    $payConfirmParameter = $saferpay->verifyPayConfirm($request->query->get('DATA'), $request->query->get('SIGNATURE'));

                    if(true === $this->validatePayConfirmParameter($payConfirmParameter, $payInitParameter)){
                        if(false === $this->doCompletePayment()){
                            return new PaymentFinishedResponse(PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_COMPLETION);
                        }

                        $payCompleteResponse = $saferpay->payCompleteV2($payConfirmParameter, 'Settlement');
                        if($payCompleteResponse->getResult() != '0'){
                            return new PaymentFinishedResponse();
                        }

                        return new PaymentFinishedResponse(PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_COMPLETION);
                    }

                    $saferpay->payCompleteV2($payConfirmParameter, 'Cancel');
                    return new PaymentFinishedResponse(PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_VALIDATION);
                }catch(\Exception $e){
                    return new PaymentFinishedResponse(PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_VALIDATION);
                }
            break;
            case PaymentFinishedResponse::STATUS_ERROR:
                return new ErrorRedirectResponse(array('paymenterror' => $request->query->get('error')));
            break;
        }

        try {
            if($url = $saferpay->createPayInit($payInitParameter)){
                return new RedirectResponse($url);
            }
            return new ErrorRedirectResponse(array('paymenterror' => 'connectionerror'));
        }catch(\Exception $e){
            return new ErrorRedirectResponse(array('paymenterror' => 'connectionerror'));
        }
    }

    /**
     * @param PayConfirmParameterInterface $payConfirmParameter
     * @param PayInitParameterInterface $payInitParameter
     * @return bool
     */
    protected function validatePayConfirmParameter(PayConfirmParameterInterface $payConfirmParameter, PayInitParameterInterface $payInitParameter)
    {
        return $payConfirmParameter->getAmount() == $payInitParameter->getAmount() && $payConfirmParameter->getCurrency() == $payInitParameter->getCurrency();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('method', 'choice', array(
            'choices' => $this->getPaymentMethods(),
            'expanded' => true
        ));
    }

    /**
     * @param Context $context
     * @param CartInterface $cart
     * @return PayInitParameterWithDataInterface
     */
    protected function getPayInitParameter(Context $context, CartInterface $cart)
    {
        $payInitParameter = $this->payInitParameterFactory->createPayInitParameter();

        $currentRouteName = $context->getCurrentRouteName();
        $invoiceAddress = $cart->getInvoiceAddress();
        $router = $this->getRouter();

        $providerSet = null;

        if($this->isTestMode()){
            $payInitParameter->setAccountid('99867-94913159');
            $payInitParameter->setPaymentmethods(6);
        }else{
            $serviceData = $cart->getPaymentOptionStrategyServiceData();
            if(isset($serviceData['method'])){
                $payInitParameter->setPaymentmethods($serviceData['method']);
            }
        }

        $payInitParameter
            ->setAmount(round($cart->getTotalWithTax()*100))
            ->setDescription(sprintf('Order %s', $cart->getId()))
            ->setOrderid($cart->getId())
            ->setSuccesslink($router->generate($currentRouteName, array('status' => PaymentFinishedResponse::STATUS_OK), true))
            ->setFaillink($router->generate($currentRouteName, array('status' => PaymentFinishedResponse::STATUS_ERROR, 'error' => 'fail'), true))
            ->setBacklink($router->generate($currentRouteName, array('status' => PaymentFinishedResponse::STATUS_ERROR, 'error' => 'back'), true))
            ->setFirstname($invoiceAddress->getFirstname())
            ->setLastname($invoiceAddress->getLastname())
            ->setStreet($invoiceAddress->getStreet())
            ->setZip($invoiceAddress->getZip())
            ->setCity($invoiceAddress->getCity())
            ->setCountry($invoiceAddress->getCountry())
            ->setEmail($invoiceAddress->getEmail())
            ->setCurrency(strtoupper($cart->getCurrency()))
        ;

        if($invoiceAddress->isTitleWoman()){
            $payInitParameter->setGender('F');
        }elseif($invoiceAddress->isTitleMan()){
            $payInitParameter->setGender('M');
        }elseif($invoiceAddress->isTitleCompany()){
            $payInitParameter->setGender('C');
        }

        return $payInitParameter;
    }
}