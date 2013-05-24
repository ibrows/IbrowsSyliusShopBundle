<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Payment;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

class InvoicePaymentOptionCartStrategy extends AbstractPaymentOptionCartStrategy
{
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
        return array();
    }

    /**
     * @param Request $request
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return InvoicePaymentOptionCartStrategy
     */
    public function pay(Request $request, CartInterface $cart, CartManager $cartManager)
    {
        return true;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('method', 'choice', array(
            'choices' => array(
                'huhu' => 'haha',
                'foo' => 'bar'
            ),
            'expanded' => true
        ));
    }
}