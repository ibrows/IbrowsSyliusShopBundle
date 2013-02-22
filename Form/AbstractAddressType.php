<?php

namespace Ibrows\SyliusShopBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractAddressType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'email');
    }
}