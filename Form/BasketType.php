<?php

namespace Ibrows\SyliusShopBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\AbstractType;

class BasketType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('items', 'collection', array(
                'entry_type' => $options['basketItemType']
            ))
            ->add('continue', 'submit')
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ibr_sylius_basket';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver->setDefaults(array(
            'basketItemType' => null
        )));
    }
}