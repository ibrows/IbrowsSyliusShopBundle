<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Payment;

use Ibrows\DataTrans\Api\Authorization\Authorization;
use Ibrows\DataTrans\Api\Authorization\Data\Request\AbstractAuthorizationRequest;
use Ibrows\DataTrans\Api\Authorization\Data\Request\StandardAuthorizationRequest;
use Ibrows\DataTrans\DataInterface;
use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\ErrorRedirectResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\PaymentFinishedResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\SelfRedirectResponse;
use Ibrows\SyliusShopBundle\CountryCode\CountryCode;
use Ibrows\SyliusShopBundle\Entity\Payment;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DatatransPaymentOptionCartStrategy extends AbstractPaymentOptionCartStrategy
{
    /**
     * @var Authorization
     */
    protected $authorization;

    /**
     * @var array
     */
    protected $paymentMethods;

    /**
     * @var string
     */
    protected $defaultPaymentMethod;

    /**
     * @var string
     */
    protected $merchandId;

    /**
     * @param Authorization $authorization
     * @param array         $paymentMethods
     * @param string        $defaultPaymentMethod
     * @param string        $merchandId
     */
    public function __construct(
        Authorization $authorization,
        array $paymentMethods = array(DataInterface::PAYMENTMETHOD_VISA, DataInterface::PAYMENTMETHOD_MASTERCARD),
        $defaultPaymentMethod = DataInterface::PAYMENTMETHOD_VISA,
        $merchandId = '1000011011'
    ) {
        $this->authorization = $authorization;
        $this->paymentMethods = $paymentMethods;
        $this->defaultPaymentMethod = $defaultPaymentMethod;
        $this->merchandId = $merchandId;
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
     */
    public function setPaymentMethods(array $paymentMethods)
    {
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * @param CartInterface $cart
     *
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

        return $methods[$data['method']].' (Datatrans)';
    }

    /**
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     *
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
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     *
     * @return bool
     */
    public function isPossible(CartInterface $cart, CartManager $cartManager)
    {
        return true;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     *
     * @return AdditionalCartItemInterface[]
     */
    public function compute(CartInterface $cart, CartManager $cartManager)
    {
        $data = $cart->getPaymentOptionStrategyServiceData();
        if (!$this->isParentVisible() && !isset($data['method'])) {
            $this->removeStrategy($cart);
        }

        return array();
    }

    /**
     * @param Context       $context
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     *
     * @throws \Exception if createPayInit creates an exception
     *
     * @return RedirectResponse|PaymentFinishedResponse|ErrorRedirectResponse|SelfRedirectResponse
     */
    public function pay(Context $context, CartInterface $cart, CartManager $cartManager)
    {
        $authorizationRequest = $this->getAuthorizationRequest($context, $cart);
        $request = $context->getRequest();

        if ($request->isMethod('POST') && $status = $request->get('status')) {
            switch ($status) {
                case $this->getPaymentFinishedResponseStatusOkValue():
                    return new PaymentFinishedResponse(
                        $this->getServiceId(),
                        PaymentFinishedResponse::STATUS_OK,
                        null,
                        $cart->getPaymentOptionStrategyServiceData(),
                        $request->request->all()
                    );
                    break;
                case $this->getPaymentFinishedResponseStatusCancelValue():
                    $data = $request->request->all();
                    $canceledAuthorizationResponse = $this->authorization->unserializeCancelAuthorizationResponse($data);

                    $payment = new Payment();
                    $payment->setData($data);
                    $payment->setStrategyId($this->getServiceId());
                    $cart->addPayment($payment);
                    $cartManager->persistCart(false);

                    return new ErrorRedirectResponse(
                        array(
                            'status' => $canceledAuthorizationResponse->getStatus(),
                        )
                    );
                    break;
                case $this->getPaymentFinishedResponseStatusErrorValue():
                    $data = $request->request->all();
                    $failedAuthorizationResponse = $this->authorization->unserializeFailedAuthorizationResponse($data);

                    $payment = new Payment();
                    $payment->setData($data);
                    $payment->setStrategyId($this->getServiceId());
                    $cart->addPayment($payment);
                    $cartManager->persistCart(false);

                    return new ErrorRedirectResponse(
                        array(
                            'status' => $failedAuthorizationResponse->getStatus(),
                            'response' => array(
                                'errorCode' => $failedAuthorizationResponse->getErrorCode(),
                                'errorMessage' => $failedAuthorizationResponse->getErrorMessage(),
                                'errorDetail' => $failedAuthorizationResponse->getErrorDetail(),
                            ),
                        )
                    );
                    break;
            }
        }

        try {
            $violations = $this->getAuthorization()->validateAuthorizationRequest($authorizationRequest);
            if ($violations->count()) {
                $violationsArr = [];
                for ($i = 0; $i < $violations->count(); $i++) {
                    $violationsArr[] = $violations->get($i);
                }
                return new ErrorRedirectResponse([
                    'violations' => $violationsArr,
                ]);
            }

            $authorizationRequestData = $this->serializeAuthorizationRequest($authorizationRequest);
            if ($url = DataInterface::URL_AUTHORIZATION.'?'.http_build_query($authorizationRequestData)) {
                return new RedirectResponse($url);
            }

            return new ErrorRedirectResponse(array('paymenterror' => 'connectionerror'));
        } catch (\Exception $e) {
            return new ErrorRedirectResponse(array('paymenterror' => 'connectionerror'));
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$this->isParentVisible()) {
            $builder->add(
                'method',
                'choice',
                array(
                    'choices' => $this->getPaymentMethods(),
                    'expanded' => true,
                )
            );
        }
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
     */
    public function setDefaultPaymentMethod($defaultPaymentMethod)
    {
        $this->defaultPaymentMethod = $defaultPaymentMethod;
    }

    /**
     * @return string
     */
    public function getMerchandId()
    {
        return $this->merchandId;
    }

    /**
     * @param string $merchandId
     */
    public function setMerchandId($merchandId)
    {
        $this->merchandId = $merchandId;
    }

    /**
     * @return Authorization
     */
    protected function getAuthorization()
    {
        return $this->authorization;
    }

    /**
     * @param Authorization $authorization
     */
    protected function setAuthorization(Authorization $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * @param Context       $context
     * @param CartInterface $cart
     *
     * @return StandardAuthorizationRequest
     */
    protected function getAuthorizationRequest(Context $context, CartInterface $cart)
    {
        $currentRouteName = $context->getCurrentRouteName();
        $invoiceAddress = $cart->getInvoiceAddress();
        $router = $this->getRouter();

        $authorizationRequest = $this->authorization->createStandardAuthorizationRequest(
            $this->getMerchandId(),
            round($cart->getAmountToPay() * 100),
            $cart->getCurrency(),
            'Order #'.$cart->getId(),
            $router->generate($currentRouteName, array('status' => PaymentFinishedResponse::STATUS_OK), true),
            $router->generate($currentRouteName, array('status' => PaymentFinishedResponse::STATUS_ERROR), true),
            $router->generate($currentRouteName, array('status' => PaymentFinishedResponse::STATUS_CANCEL), true)
        );

        $authorizationRequest->setUppWebResponseMethod(DataInterface::RESPONSEMETHOD_POST);
        $authorizationRequest->setUppCustomerDetails(DataInterface::CUSTOMERDETAIL_TRUE);

        $authorizationRequest->setUppCustomerFirstName($invoiceAddress->getFirstname());
        $authorizationRequest->setUppCustomerLastName($invoiceAddress->getLastname());
        $authorizationRequest->setUppCustomerStreet($invoiceAddress->getStreet());
        $authorizationRequest->setUppCustomerCity($invoiceAddress->getCity());
        $authorizationRequest->setUppCustomerZipCode($invoiceAddress->getZip());
        $authorizationRequest->setUppCustomerCountry(CountryCode::getAlpha3FromAlpha2($invoiceAddress->getCountry()) ?: 'CHE');
        $authorizationRequest->setUppCustomerEmail($invoiceAddress->getEmail());
        $authorizationRequest->setUppCustomerLanguage(strtolower(substr($context->getRequest()->getLocale(), 0, 2)));

        return $authorizationRequest;
    }

    /**
     * @param AbstractAuthorizationRequest $authorizationRequest
     * @param Context $context
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return array
     */
    protected function serializeAuthorizationRequest(AbstractAuthorizationRequest $authorizationRequest, Context $context, CartInterface $cart, CartManager $cartManager)
    {
        return $this->getAuthorization()->serializeAuthorizationRequest($authorizationRequest);
    }

    protected function getPaymentFinishedResponseStatusOkValue()
    {
        return PaymentFinishedResponse::STATUS_OK;
    }

    protected function getPaymentFinishedResponseStatusCancelValue()
    {
        return PaymentFinishedResponse::STATUS_CANCEL;
    }

    protected function getPaymentFinishedResponseStatusErrorValue()
    {
        return PaymentFinishedResponse::STATUS_ERROR;
    }
}
