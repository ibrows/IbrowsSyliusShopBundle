<?php

namespace Ibrows\SyliusShopBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;

class BasketType extends AbstractType
{
    /**
     * @var FormTypeInterface
     */
    protected $basketItemType;

    /**
     * @param FormTypeInterface $basketItemType
     */
    public function __construct(FormTypeInterface $basketItemType)
    {
        $this->basketItemType = $basketItemType;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('items', 'collection', array(
                'type' => $this->basketItemType
            ))
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ibr_sylius_basket';
    }
}