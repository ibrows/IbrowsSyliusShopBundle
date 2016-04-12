<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Payment;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\ErrorRedirectResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\PaymentFinishedResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response\SelfRedirectResponse;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

class InvoicePaymentOptionCartStrategy extends AbstractPaymentOptionCartStrategy
{
    protected $skonto = 0;
    protected $totalMethod = 'getTotal';
    protected $roundpercent = true;

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
        if (!$this->skonto) {
            return array();
        }
        $totalmethod = $this->totalMethod;
        if (!method_exists($cart, $totalmethod)) {
            throw new \Exception("$totalmethod dont exists in $cart, use setTotalMethod() to set one wich exists");
        }
        $total = $cart->$totalmethod();
        $costs = $this->skonto;
        if (stripos($costs, '%') !== false) {
            $percent = intval($costs);
            $costs = $total * $percent / 100;
            if ($this->roundpercent) {
                $costs = self::roundfivers($costs);
            }
        }

        $costs = $costs * -1;

        $item = $this->createAdditionalCartItem($costs);

        $item->setText($this->getServiceId());
        $item->setStrategyData(array('costs' => $costs, 'total' => $total, 'skonto' => $this->skonto));

        return array(
                $item,
        );
    }

    /**
     * @param number $value
     *
     * @return number
     */
    private static function roundfivers($value)
    {
        return round(2 * $value, 1) / 2;
    }

    /**
     * @param Context       $context
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     *
     * @return RedirectResponse|PaymentFinishedResponse|ErrorRedirectResponse|SelfRedirectResponse
     */
    public function pay(Context $context, CartInterface $cart, CartManager $cartManager)
    {
        return new PaymentFinishedResponse($this->getServiceId(), PaymentFinishedResponse::STATUS_ERROR, PaymentFinishedResponse::ERROR_COMPLETION);
    }

    public function getSkonto()
    {
        return $this->skonto;
    }

    public function setSkonto($skonto)
    {
        $this->skonto = $skonto;

        return $this;
    }

    public function getTotalMethod()
    {
        return $this->totalMethod;
    }

    public function setTotalMethod($totalMethod)
    {
        $this->totalMethod = $totalMethod;

        return $this;
    }

    public function isRoundpercent()
    {
        return $this->roundpercent;
    }

    public function setRoundpercent($roundpercent)
    {
        $this->roundpercent = $roundpercent;

        return $this;
    }
}
