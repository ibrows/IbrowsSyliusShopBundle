<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Payment;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\PaymentFinishedResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\SelfRedirectResponse;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartPaymentOptionStrategyInterface;
use Payment\HttpClient\GuzzleClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormBuilderInterface;
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
     * @param Request $request
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return mixed
     */
    public function pay(Request $request, CartInterface $cart, CartManager $cartManager)
    {
        $session = $request->getSession();
        $sessionKey = $this->getSessionKey();

        $saferpay = $this->getSaferpay();
        $saferpay->setLogger($this->getLogger());
        $saferpay->setHttpClient(new GuzzleClient());
        $saferpay->setData($session->get($sessionKey));

        if($request->query->get('status') == 'success'){
            if($saferpay->confirmPayment($request->query->get('DATA'), $request->query->get('SIGNATURE')) != ''){
                if($saferpay->completePayment() != ''){
                    $session->remove($sessionKey);
                    $cart->setPayed();
                    return new PaymentFinishedResponse();
                }else{
                    return new PaymentFinishedResponse(PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_CONFIRMATION);
                }
            }else{
                return new PaymentFinishedResponse(PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_COMPLETION);
            }
        }else{
            $invoiceAddress = $cart->getInvoiceAddress();

            if($this->isTestMode()){
                $providerSet = 6;
            }else{
                $providerSet = 6;
            }

            $url = $saferpay->initPayment($saferpay->getKeyValuePrototype()->all(array(
                'AMOUNT' => $cart->getTotalWithTax(),
                'DESCRIPTION' => sprintf('Bestellnummer: %s', $cart->getId()),
                'ORDERID' => $cart->getId(),
                'SUCCESSLINK' => $this->generateUrl('checkout_saferpay', array('status' => 'success'), true),
                'FAILLINK' => $this->generateUrl('checkout_summary', array('status' => 'fail'), true),
                'BACKLINK' => $this->generateUrl('checkout_summary', array('status' => 'abort'), true),
                'FIRSTNAME' => $invoiceAddress->getFirstname(),
                'LASTNAME' => $invoiceAddress->getLastname(),
                'STREET' => $invoiceAddress->getStreet(),
                'ZIP' => $invoiceAddress->getZip(),
                'CITY' => $invoiceAddress->getCity(),
                'COUNTRY' => $invoiceAddress->getCountry(),
                'EMAIL' => $invoiceAddress->getEmail(),
                //'GENDER' => $invoiceAddress->getTitle() == Address::TITLE_MISTER ? "M" : "F",
                'PAYMENTMETHODS' => $providerSet,
                'CURRENCY' => $cart->getCurrency()
            )));

            $session->set($sessionKey, $saferpay->getData());

            if($url != ''){
                return new RedirectResponse($url, 302);
            }else{
                return $this->redirect($this->generateUrl('checkout_summary', array('status' => 'connectionerror')));
            }
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
     * @param CartInterface $cart
     * @return string
     */
    public function getFullPaymentMethodName(CartInterface $cart)
    {
        $data = $cart->getPaymentOptionStrategyServiceData();
        if(!isset($data['method'])){
            return parent::getFullPaymentMethodName($cart);
        }
        return $this->getServiceId().'.'.$data['method'];
    }
}