<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Payment;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\PaymentFinishedResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\SelfRedirectResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\ErrorRedirectResponse;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use \Ticketpark\SaferpayJson\PaymentPage\InitializeRequest;
use \Ticketpark\SaferpayJson\Container;
use \Ticketpark\SaferpayJson\Message\ErrorResponse;
use \Ticketpark\SaferpayJson\PaymentPage\AssertRequest;
use \Ticketpark\SaferpayJson\Transaction\CaptureRequest;

class SaferpayPaymentOptionCartStrategy extends AbstractPaymentOptionCartStrategy
{
    /**
     * @var array
     */
    protected $paymentMethods;

    /**
     * @var string
     */
    protected $defaultPaymentMethod;

    /**
     * @var bool
     */
    protected $doCompletePayment;

    /**
     * @var CartInterface
     */
    protected $cart;

    /**
     * @var CartManager
     */
    protected $cartManager;

    /**
     * @var array
     */
    protected $paymentParameter;

    /**
     * @var array
     */
    protected $credentials;

    /**
     * @param array $paymentMethods
     * @param bool $doCompletePayment
     * @param string $testAccountId
     */
    public function __construct( array $credentials, $saferpay_live_mode = false, array $paymentMethods, $doCompletePayment = true)
    {
        $this->paymentMethods = $paymentMethods;
        $this->setParentVisible(false);
        $this->setDoCompletePayment($doCompletePayment);
        $this->setTestMode($saferpay_live_mode ? false : true);
        $this->setCredentials($credentials);
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
     * @param CartInterface $cart
     * @return string
     */
    public function getTranslationKey(CartInterface $cart)
    {
        $data = $cart->getPaymentOptionStrategyServiceData();
        if (!isset($data['method'])) {
            return parent::getTranslationKey($cart);
        }

        $methods = $this->getPaymentMethods();

        if (!isset($methods[$data['method']])) {
            return parent::getTranslationKey($cart);
        }

        return $methods[$data['method']] . ' (Saferpay)';
    }

    /**
     * @param array $credentials
     * @return SaferpayPaymentOptionCartStrategy
     */
    public function setCredentials(array $credentials)
    {
        $this->credentials = $credentials;
        return $this;
    }

    /**
     * @return array
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return bool
     */
    public function accept(CartInterface $cart, CartManager $cartManager)
    {
        $selectedServiceId = $cart->getPaymentOptionStrategyServiceId();
        if ($selectedServiceId == $this->getServiceId()) {
            return true;
        }

        if (!$selectedServiceId && $this->isDefault()) {
            $cart->setPaymentOptionStrategyServiceId($this->getServiceId());
            $cart->setPaymentOptionStrategyServiceData(array('method' => $this->getDefaultPaymentMethod()));
            return true;
        }

        return false;
    }


    /**
     * @return CartManager
     */
    protected function getCartManager()
    {
        return $this->cartManager;
    }

    /**
     * @param CartManager $cartManager
     * @return SaferpayPaymentOptionCartStrategy
     */
    protected function setCartManager(CartManager $cartManager)
    {
        $this->cartManager = $cartManager;
        return $this;
    }

    /**
     * @return CartInterface
     */
    protected function getCart()
    {
        return $this->cart;
    }

    /**
     * @param CartInterface $cart
     * @return SaferpayPaymentOptionCartStrategy
     */
    protected function setCart(CartInterface $cart)
    {
        $this->cart = $cart;
        return $this;
    }

    /**
     * @return array
     */
    protected function getPaymentParameter()
    {
        return $this->paymentParameter;
    }

    /**
     * @param array $paymentParameter
     * @return SaferpayPaymentOptionCartStrategy
     */
    protected function setPaymentParameter(array $paymentParameter)
    {
        $this->paymentParameter = $paymentParameter;
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
        if (!isset($data['method'])) {
            $this->removeStrategy($cart);
        }
        return array();
    }

    /**
     * @param Context $context
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @throws \Exception if createPayInit creates an exception
     * @return RedirectResponse|PaymentFinishedResponse|ErrorRedirectResponse|SelfRedirectResponse
     */
    public function pay(Context $context, CartInterface $cart, CartManager $cartManager)
    {
        $request = $context->getRequest();
        $this->setCart($cart);
        $this->setCartManager($cartManager);
        $this->computeIntiParameter($context);

        // Routes the response result comming from Saferpay
        switch ($request->query->get('status')) {
            case PaymentFinishedResponse::STATUS_OK:
                // This will be executed if the Redirect response form Saferpay is Successful
                try {
                    $PaymentFinishedResponseData = $this->getPaymentInformations();

                    if ($this->verifyPayConfirm()) {
                        if (false === $this->doCompletePayment()) {
                            return new PaymentFinishedResponse($this->getServiceId(), PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_COMPLETION, $cart->getPaymentOptionStrategyServiceData(), $PaymentFinishedResponseData);
                        }
                        if(!$this->captureTransaction()){
                            return new PaymentFinishedResponse($this->getServiceId(), PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_COMPLETION, $cart->getPaymentOptionStrategyServiceData(), $PaymentFinishedResponseData);
                        }
                        return new PaymentFinishedResponse($this->getServiceId(), PaymentFinishedResponse::STATUS_OK, null, $cart->getPaymentOptionStrategyServiceData(), $PaymentFinishedResponseData);;
                    }else{
                        return new PaymentFinishedResponse($this->getServiceId(), PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_COMPLETION, $cart->getPaymentOptionStrategyServiceData(), $PaymentFinishedResponseData);
                    }

                    return new PaymentFinishedResponse($this->getServiceId(), PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_VALIDATION, $cart->getPaymentOptionStrategyServiceData(), $PaymentFinishedResponseData);
                } catch (\Exception $e) {
                    return new PaymentFinishedResponse($this->getServiceId(), PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_VALIDATION, $cart->getPaymentOptionStrategyServiceData(), $PaymentFinishedResponseData);
                }
                break;
            case PaymentFinishedResponse::STATUS_ERROR:
                // This will be executed if the Redirect response form Saferpay is contains an Error 
                return new ErrorRedirectResponse(array('paymenterror' => $request->query->get('error')));
                break;
        }

        //This send the Customer to Saferpay Payment including the preped Data
        return $this->requestSaferpay();
    }

    protected function requestSaferpay()
    {
        try {
            $credentials = $this->getCredentials();
            $paymentParameter = $this->getPaymentParameter();
            // Request Saferpay and get Url for Redirect
            $response = (new InitializeRequest($credentials['saferpay_api_key'], $credentials['saferpay_api_secret'], $this->isTestMode()))
            ->setRequestHeader($paymentParameter['requestHeader'])
            ->setPayment($paymentParameter['payment'])
            ->setTerminalId($credentials['saferpay_terminal_id'])
            ->setReturnUrls($paymentParameter['returnUrls'])
            ->setNotification($paymentParameter['notification'])
            ->setPayer($paymentParameter['payer'])
            ->execute();

            if ($response) {
                if($response instanceof ErrorResponse ){
                    return new ErrorRedirectResponse(array('paymenterror' => 'connectionerror'));
                }
                $this->saveToken($response->getToken() );
                // redirect User to Saferpay
                return new RedirectResponse($response->getRedirectUrl());
            }
            return new ErrorRedirectResponse(array('paymenterror' => 'connectionerror'));
        } catch (\Exception $e) {
            return new ErrorRedirectResponse(array('paymenterror' => 'connectionerror'));
        }
    }
    /**
    * @param string $token
    * @return SaferpayPaymentOptionCartStrategy
    */
    protected function saveToken($token)
    {
        $paymentOptionStrategyServiceData= $this->getCart()->getPaymentOptionStrategyServiceData();
        $paymentOptionStrategyServiceData['token'] = $token;
        $this->getCart()->setPaymentOptionStrategyServiceData($paymentOptionStrategyServiceData);
        $this->getCartManager()->persistCart();
        return $this;
    }

    /**
    * @param string $id 
    * @param string $status
    * @return SaferpayPaymentOptionCartStrategy
    */
    protected function saveTransaction($id, $status)
    {
        $paymentOptionStrategyServiceData= $this->getCart()->getPaymentOptionStrategyServiceData();
        $paymentOptionStrategyServiceData['transactionId'] = $id;
        $paymentOptionStrategyServiceData['transactionStatus'] = $status;
        $this->getCart()->setPaymentOptionStrategyServiceData($paymentOptionStrategyServiceData);
        $this->getCartManager()->persistCart();
        return $this;
    }

    protected function verifyPayConfirm()
    {
        $paymentOptionStrategyServiceData= $this->getCart()->getPaymentOptionStrategyServiceData();
        if(!array_key_exists('token',$paymentOptionStrategyServiceData)){
            return false;
        }
        $token = $paymentOptionStrategyServiceData['token'];
        $credentials = $this->getCredentials();

        // Prepare the assert request
        // See http://saferpay.github.io/jsonapi/#Payment_v1_PaymentPage_Assert
        $requestHeader = (new Container\RequestHeader())
            ->setCustomerId($credentials['saferpay_customer_id'])
            ->setRequestId(uniqid());

        $response = (new AssertRequest($credentials['saferpay_api_key'], $credentials['saferpay_api_secret'], $this->isTestMode()))
            ->setRequestHeader($requestHeader)
            ->setToken($token)
            ->execute();
        
        // Check for successful response
        if ($response instanceof ErrorResponse) {
            die($response->getErrorMessage());
        }
        if($transaction = $response->getTransaction()){
            if($transaction->getStatus() == 'AUTHORIZED'){
                // Save Transaction Id for documentation to the cart
                $this->saveTransaction($transaction->getId(), $transaction->getStatus());

                return $response;
            }
        }
        return false;
    }

    protected function captureTransaction()
    {
        $paymentOptionStrategyServiceData= $this->getCart()->getPaymentOptionStrategyServiceData();
        if(!array_key_exists('transactionId',$paymentOptionStrategyServiceData) || !array_key_exists('transactionStatus',$paymentOptionStrategyServiceData)){
            return false;
        }
        $transactionId = $paymentOptionStrategyServiceData['transactionId'];
        $transactionStatus = $paymentOptionStrategyServiceData['transactionStatus'];

        $credentials = $this->getCredentials();

        // Prepare the capture request
        // https://saferpay.github.io/jsonapi/#Payment_v1_Transaction_Capture
        $requestHeader = (new Container\RequestHeader())
            ->setCustomerId($credentials['saferpay_customer_id'])
            ->setRequestId(uniqid());

        $transactionReference = (new Container\TransactionReference())
            ->setTransactionId($transactionId);

        $response = (new CaptureRequest($credentials['saferpay_api_key'], $credentials['saferpay_api_secret'], $this->isTestMode()))
            ->setRequestHeader($requestHeader)
            ->setTransactionReference($transactionReference)
            ->execute();
        
        // Check for successful response
        if ($response instanceof ErrorResponse) {
            return false;
        }
        return true;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('method', 'choice', array(
            'choices' => array_flip($this->getPaymentMethods()),
            'expanded' => true
        ));
    }

    /**
     * @param Context $context
     * @return SaferpayPaymentOptionCartStrategy
     */
    protected function computeIntiParameter(Context $context)
    {
        $credentials = $this->getCredentials();
        $cart = $this->getCart();

        $currentRouteName = $context->getCurrentRouteName();
        $router = $this->getRouter();

        $successUrl = $router->generate($currentRouteName, ['status' => PaymentFinishedResponse::STATUS_OK], UrlGeneratorInterface::ABSOLUTE_URL);
        $failUrl = $router->generate($currentRouteName, ['status' => PaymentFinishedResponse::STATUS_ERROR, 'error' => 'fail'], UrlGeneratorInterface::ABSOLUTE_URL);
        $backUrl = $router->generate($currentRouteName, ['status' => PaymentFinishedResponse::STATUS_ERROR, 'error' => 'back'], UrlGeneratorInterface::ABSOLUTE_URL);
        
        $requestHeader = (new Container\RequestHeader())
            ->setCustomerId($credentials['saferpay_customer_id'])
            ->setRequestId(uniqid());


        $amount = (new Container\Amount())
            ->setCurrencyCode(strtoupper($cart->getCurrency()))
            ->setValue(round($cart->getAmountToPay() * 100)); // amount in cents


        $payment = (new Container\Payment())
            ->setAmount($amount)
            ->setOrderId($cart->getId())
            ->setDescription(sprintf('Order %s', $cart->getId()));

        $invoiceAddress = $cart->getInvoiceAddress();
        $address = (new Container\Address())
            ->setFirstName($invoiceAddress->getFirstname())
            ->setLastName($invoiceAddress->getLastname())
            ->setStreet($invoiceAddress->getStreet())
            ->setZip($invoiceAddress->getZip())
            ->setCity($invoiceAddress->getCity())
            ->setCountryCode($invoiceAddress->getCountry())
            ->setGender('MALE');

        $payer = (new Container\Payer())
            ->setLanguageCode($context->getRequest()->getLocale())
            ->setBillingAddress($address);

        $returnUrls = (new Container\ReturnUrls())
            ->setSuccess($successUrl)
            ->setFail($failUrl)
            ->setAbort($backUrl);

        $notification = (new Container\Notification())
            ->setNotifyUrl('https://www.mysite.ch/notification');

        $this->setPaymentParameter([
            'requestHeader' => $requestHeader,
            'payment' => $payment,
            'returnUrls' => $returnUrls,
            'notification' => $notification,
            'payer' => $payer
        ]);
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultPaymentMethod()
    {
        return $this->defaultPaymentMethod;
    }

    /**
     * @param string $defaultPaymentMethod
     * @return SaferpayPaymentOptionCartStrategy
     */
    public function setDefaultPaymentMethod($defaultPaymentMethod)
    {
        $this->defaultPaymentMethod = $defaultPaymentMethod;
        return $this;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }

    public function getBlockPrefix()
    {
        return "";
    }
    /**
     * @return array
     */
    protected function getPaymentInformations()
    {
        $paymentParameter = $this->paymentParameter;
        $paymentInformation = [];
        if( $paymentParameter != null ){
            if(array_key_exists( 'payment', $paymentParameter )){
                $paymentObj = $paymentParameter['payment'];
                $amount = $paymentObj->getAmount();
                $payment['value'] = $amount->getValue();
                $payment['currencyCode'] = $amount->getCurrencyCode();
                $payment['orderId'] = $paymentObj->getOrderId();
                $payment['description'] = $paymentObj->getDescription();
                $payment['payerNote'] = $paymentObj->getPayerNote();
                $paymentInformation['payment'] = $payment;
            }
            if(array_key_exists( 'payer', $paymentParameter )){
                $payerObj = $paymentParameter['payer'];
                $payer['languageCode'] = $payerObj->getLanguageCode();
                $address = $payerObj->getBillingAddress();
                $payer['firstName'] = $address->getFirstName();
                $payer['lastName'] = $address->getLastName();
                $payer['street'] = $address->getStreet();
                $payer['zip'] = $address->getZip();
                $payer['city'] = $address->getCity();
                $payer['countryCode'] = $address->getCountryCode();

                $paymentInformation['payer'] = $payer;
            }
        }

       return [
        'payConfirmParameter' => $paymentInformation
       ];
    }
}
