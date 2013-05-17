<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartPaymentStrategyInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartPaymentStrategyResponseInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractSaferpayPaymentCartStrategy extends AbstractCartStrategy implements CartPaymentStrategyInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var string
     */
    protected $sessionKey;

    /**
     * @return RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param RouterInterface $router
     * @return AbstractSaferpayPaymentCartStrategy
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
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
     * @return AbstractSaferpayPaymentCartStrategy
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
     * @return bool
     */
    public function accept(CartInterface $cart, CartManager $cartManager)
    {
        return $cart->getPaymentStrategyServiceId() == $this->getServiceId();
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return AdditionalCartItemInterface[]
     */
    public function compute(CartInterface $cart, CartManager $cartManager)
    {
        return array();
    }

    /**
     * @param Request $request
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return CartPaymentStrategyResponseInterface
     */
    public function pay(Request $request, CartInterface $cart, CartManager $cartManager)
    {
        return;

        $session = $request->getSession();

        $sessionKey = $this->getSessionKey();
        $saferpay = $this->getSaferpay($session, $sessionKey);

        if($request->query->get('status') == 'success'){
            if($saferpay->confirmPayment($request->query->get('DATA'), $request->query->get('SIGNATURE')) != ''){
                if($saferpay->completePayment() != ''){
                    $session->remove($sessionKey);

                    $cart->setPayed();
                    return $this->redirect($this->getWizard()->getNextStepUrl());
                }else{
                    return $this->redirect($this->generateUrl('checkout_summary', array('status' => 'confirmerror')));
                }
            }else{
                return $this->redirect($this->generateUrl('checkout_summary', array('status' => 'completeerror')));
            }
        }else{
            $cartManager = $this->getCartManager();
            $billingAddress = $cart->getBillingAddress();

            if($this->getParameter('cnc_saferpay.usetestcard') == true){
                $providerSet = 6;
            }else{
                $providerSet = $cart->getProviderSet();
                if(!$providerSet){
                    $providerSet = $this->getParameter('cnc_saferpay.defaultproviderset');
                }
            }

            $url = $saferpay->initPayment($saferpay->getKeyValuePrototype()->all(array(
                'AMOUNT' => round($cartManager->getTotalWithShippingCostsAndTax($cart)*100),
                'DESCRIPTION' => sprintf('Bestellnummer: %s', $cart->getOrderId()),
                'ORDERID' => $cart->getOrderId(),
                'SUCCESSLINK' => $this->generateUrl('checkout_saferpay', array('status' => 'success'), true),
                'FAILLINK' => $this->generateUrl('checkout_summary', array('status' => 'fail'), true),
                'BACKLINK' => $this->generateUrl('checkout_summary', array('status' => 'abort'), true),
                'FIRSTNAME' => $billingAddress->getPrename(),
                'LASTNAME' => $billingAddress->getName(),
                'STREET' => $billingAddress->getStreet(),
                'ZIP' => $billingAddress->getZip(),
                'CITY' => $billingAddress->getCity(),
                'COUNTRY' => $billingAddress->getCountry(),
                'EMAIL' => $billingAddress->getEmail(),
                'GENDER' => $billingAddress->getTitle() == Address::TITLE_MISTER ? "M" : "F",
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
}