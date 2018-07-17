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
use Symfony\Component\OptionsResolver\OptionsResolver;

class SaferpayPaymentOptionCartStrategy extends AbstractPaymentOptionCartStrategy
{
    /**
     * @var Saferpay
     */
    protected $saferpay;

    /**
     * @var string
     */
    protected $saferpayPassword;

    /**
     * @var PayInitParameterFactory
     */
    protected $payInitParameterFactory;

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
     * @var string
     */
    protected $testAccountId;

    /**
     * @param Saferpay $saferpay
     * @param PayInitParameterFactory $payInitParameterFactory
     * @param array $paymentMethods
     * @param bool $doCompletePayment
     * @param string $testAccountId
     */
    public function __construct(Saferpay $saferpay, PayInitParameterFactory $payInitParameterFactory, array $paymentMethods, $doCompletePayment = true, $testAccountId = '99867-94913159')
    {
        $this->saferpay = $saferpay;
        $this->payInitParameterFactory = $payInitParameterFactory;
        $this->paymentMethods = $paymentMethods;
        $this->setParentVisible(false);
        $this->setDoCompletePayment($doCompletePayment);
        $this->setTestAccountId($testAccountId);
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
        $saferpay = $this->getSaferpay();

        $payInitParameter = $this->getPayInitParameter($context, $cart);
        $PaymentFinishedResponseData = array('payInitParameter' => $payInitParameter->getData());

        switch ($request->query->get('status')) {
            case PaymentFinishedResponse::STATUS_OK:
                try {
                    $payConfirmParameter = $saferpay->verifyPayConfirm($request->query->get('DATA'), $request->query->get('SIGNATURE'));
                    $PaymentFinishedResponseData['payConfirmParameter'] = $payConfirmParameter->getData();

                    if (true === $this->validatePayConfirmParameter($payConfirmParameter, $payInitParameter)) {
                        if (false === $this->doCompletePayment()) {
                            return new PaymentFinishedResponse($this->getServiceId(), PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_COMPLETION, $cart->getPaymentOptionStrategyServiceData(), $PaymentFinishedResponseData);
                        }

                        $payCompleteResponse = $saferpay->payCompleteV2($payConfirmParameter, 'Settlement');
                        if ($payCompleteResponse->getResult() != '0') {
                            return new PaymentFinishedResponse($this->getServiceId(), null, null, $cart->getPaymentOptionStrategyServiceData(), $PaymentFinishedResponseData);
                        }

                        return new PaymentFinishedResponse($this->getServiceId(), PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_COMPLETION, $cart->getPaymentOptionStrategyServiceData(), $PaymentFinishedResponseData);
                    }

                    $saferpay->payCompleteV2($payConfirmParameter, 'Cancel', $this->getSaferpayPassword());
                    return new PaymentFinishedResponse($this->getServiceId(), PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_VALIDATION, $cart->getPaymentOptionStrategyServiceData(), $PaymentFinishedResponseData);
                } catch (\Exception $e) {
                    return new PaymentFinishedResponse($this->getServiceId(), PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_VALIDATION, $cart->getPaymentOptionStrategyServiceData(), $PaymentFinishedResponseData);
                }
                break;
            case PaymentFinishedResponse::STATUS_ERROR:
                return new ErrorRedirectResponse(array('paymenterror' => $request->query->get('error')));
                break;
        }

        try {
            if ($url = $saferpay->createPayInit($payInitParameter)) {
                return new RedirectResponse($url);
            }
            return new ErrorRedirectResponse(array('paymenterror' => 'connectionerror'));
        } catch (\Exception $e) {
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

        if ($this->isTestMode()) {
            $payInitParameter->setAccountid($this->getTestAccountId());
            $payInitParameter->setPaymentmethods(array($payInitParameter::PAYMENTMETHOD_SAFERPAY_TESTCARD));
        } else {
            $serviceData = $cart->getPaymentOptionStrategyServiceData();
            if (isset($serviceData['method'])) {
                $reflection = new \ReflectionClass($payInitParameter);
                if ($code = $reflection->getConstant('PAYMENTMETHOD_' . strtoupper($serviceData['method']))) {
                    $payInitParameter->setPaymentmethods(array($code));
                }
            }
        }

        $payInitParameter
            ->setAmount(round($cart->getAmountToPay() * 100))
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
            ->setLangid($context->getRequest()->getLocale());

        if ($invoiceAddress->isTitleWoman()) {
            $payInitParameter->setGender($payInitParameter::GENDER_FEMALE);
        } elseif ($invoiceAddress->isTitleMan()) {
            $payInitParameter->setGender($payInitParameter::GENDER_MALE);
        } elseif ($invoiceAddress->isTitleCompany()) {
            $payInitParameter->setGender($payInitParameter::GENDER_COMPANY);
        }

        return $payInitParameter;
    }

    /**
     * @return string
     */
    public function getTestAccountId()
    {
        return $this->testAccountId;
    }

    /**
     * @param string $testAccountId
     * @return SaferpayPaymentOptionCartStrategy
     */
    public function setTestAccountId($testAccountId)
    {
        $this->testAccountId = $testAccountId;
        return $this;
    }

    /**
     * @return string
     */
    public function getSaferpayPassword()
    {
        return $this->saferpayPassword;
    }

    /**
     * @param string $saferpayPassword
     * @return SaferpayPaymentOptionCartStrategy
     */
    public function setSaferpayPassword($saferpayPassword)
    {
        $this->saferpayPassword = $saferpayPassword;
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
}