<?php

namespace Ibrows\SyliusShopBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

class InvoiceSameAsDeliveryType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('invoiceSameAsDelivery', 'choice', array(
                'choices' => array(
                    '1' => 'invoiceSameAsDelivery.yes',
                    '0' => 'invoiceSameAsDelivery.no',
                ),
                'multiple' => false,
                'expanded' => true,
            ));
    }
}
