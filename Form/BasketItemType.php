<?php

namespace Ibrows\SyliusShopBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BasketItemType extends AbstractType
{
    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @param string $dataClass
     */
    public function __construct($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array(
                'disabled' => true
            ))
            ->add('quantity')
            ->add('unitPrice', null, array(
                'disabled' => true
            ))
            ->add('total', null, array(
                'disabled' => true
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver
            ->setDefaults(array(
                'data_class' => $this->dataClass
            ))
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ibr_sylius_basketitem';
    }
}