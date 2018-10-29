<?php

namespace Ibrows\SyliusShopBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;

class BasketItemType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity')
            ->add('delete', 'submit')
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ibr_sylius_basketitem';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver->setDefaults(array(
            'data_class' => null,
            'quantity' => null
        )));
    }
}