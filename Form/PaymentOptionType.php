<?php

namespace Ibrows\SyliusShopBundle\Form;


use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

class PaymentOptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('paymentOptions','entity',array('class'=> 'Ibrows\SyliusShopBundle\Entity\PaymentOptions'));
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'ibr_sylius_payment';
    }
}