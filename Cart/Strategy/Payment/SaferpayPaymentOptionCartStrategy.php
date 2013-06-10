<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Payment;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\PaymentFinishedResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\SelfRedirectResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\ErrorRedirectResponse;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Payment\HttpClient\GuzzleClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Payment\Saferpay\Saferpay;

class SaferpayPaymentOptionCartStrategy extends AbstractPaymentOptionCartStrategy
{
    /**
     * @var Saferpay
     */
    protected $saferpay;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $paymentMethods;

    /**
     * @var string
     */
    protected $sessionKey;

    /**
     * @param Saferpay $saferpay
     * @param LoggerInterface $logger
     * @param array $paymentMethods
     * @param string $sessionKey
     */
    public function __construct(Saferpay $saferpay, LoggerInterface $logger, array $paymentMethods, $sessionKey)
    {
        $this->saferpay = $saferpay;
        $this->logger = $logger;
        $this->paymentMethods = $paymentMethods;
        $this->sessionKey = $sessionKey;
        $this->setParentVisible(false);
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
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     * @return SaferpayPaymentOptionCartStrategy
     */
    public function setLogger($logger = null)
    {
        $this->logger = $logger;
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
    protected function setSaferpay($saferpay)
    {
        $this->saferpay = $saferpay;
        return $this;
    }

    /**
     * @return string
     */
    public function getSessionKey()
    {
        return $this->sessionKey;
    }

    /**
     * @param string $sessionKey
     * @return SaferpayPaymentOptionCartStrategy
     */
    public function setSessionKey($sessionKey)
    {
        $this->sessionKey = $sessionKey;
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
        $session = $request->getSession();
        $sessionKey = $this->getSessionKey();

        $saferpay = $this->getSaferpay();
        $saferpay->setLogger($this->getLogger());
        $saferpay->setHttpClient(new GuzzleClient());
        $saferpay->setData($session->get($sessionKey));

        $initData = $this->getInitData($context, $cart);

        switch($request->query->get('status')){
            case PaymentFinishedResponse::STATUS_OK:
                if($saferpay->confirmPayment($request->query->get('DATA'), $request->query->get('SIGNATURE')) != ''){
                    $confirmationData = $saferpay->getData()->getConfirmData();
                    $session->remove($sessionKey);

                    if($confirmationData->get('AMOUNT') != $initData['AMOUNT'] OR $confirmationData->get('CURRENCY') != $initData['CURRENCY']){
                        $saferpay->completePayment('Cancel');
                        return new PaymentFinishedResponse(PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_VALIDATION);
                    }

                    if($saferpay->completePayment() != ''){
                        return new PaymentFinishedResponse();
                    }

                    return new PaymentFinishedResponse(PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_COMPLETION);
                }
                return new PaymentFinishedResponse(PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_CONFIRMATION);
            break;
            case PaymentFinishedResponse::STATUS_ERROR:
                $session->remove($sessionKey);
                return new ErrorRedirectResponse(array('paymenterror' => $request->query->get('error')));
            break;
        }

        $url = $saferpay->initPayment($saferpay->getKeyValuePrototype()->all($initData));
        $session->set($sessionKey, $saferpay->getData());

        if($url){
            return new RedirectResponse($url);
        }else{
            return new ErrorRedirectResponse(array('paymenterror' => 'connectionerror'));
        }
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
     * @return array
     */
    protected function getInitData(Context $context, CartInterface $cart)
    {
        $currentRouteName = $context->getCurrentRouteName();
        $invoiceAddress = $cart->getInvoiceAddress();
        $router = $this->getRouter();

        $providerSet = null;

        if($this->isTestMode()){
            $providerSet = 6;
        }else{
            $serviceData = $cart->getPaymentOptionStrategyServiceData();
            if(isset($serviceData['method'])){
                $providerSet = $serviceData['method'];
            }
        }

        $initData = array(
            'AMOUNT' => round($cart->getTotalWithTax()*100),
            'DESCRIPTION' => sprintf('Bestellnummer: %s', $cart->getId()),
            'ORDERID' => $cart->getId(),
            'SUCCESSLINK' => $router->generate($currentRouteName, array('status' => PaymentFinishedResponse::STATUS_OK), true),
            'FAILLINK' => $router->generate($currentRouteName, array('status' => PaymentFinishedResponse::STATUS_ERROR, 'error' => 'fail'), true),
            'BACKLINK' => $router->generate($currentRouteName, array('status' => PaymentFinishedResponse::STATUS_ERROR, 'error' => 'back'), true),
            'FIRSTNAME' => $invoiceAddress->getFirstname(),
            'LASTNAME' => $invoiceAddress->getLastname(),
            'STREET' => $invoiceAddress->getStreet(),
            'ZIP' => $invoiceAddress->getZip(),
            'CITY' => $invoiceAddress->getCity(),
            'COUNTRY' => $invoiceAddress->getCountry(),
            'EMAIL' => $invoiceAddress->getEmail(),
            'CURRENCY' => strtoupper($cart->getCurrency())
        );

        $gender = $invoiceAddress->isTitleMan() ? 'M' : ($invoiceAddress->isTitleWoman() ? 'F' : null);
        if($gender){
            $initData['GENDER'] = $gender;
        }

        if($providerSet){
            $initData['PAYMENTMETHODS'] = $providerSet;
        }

        return $initData;
    }
}