<?php

namespace Ibrows\SyliusShopBundle\Form;

use GuzzleHttp\Collection;
use Ibrows\ShopBundle\Entity\AppCart;
use Ibrows\ShopBundle\Entity\AppCartItem;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BasketType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('items', CollectionType::class, array(
                'entry_type' => $options['basketItemTypeDataClass'],
                'entry_options' => $options['basketItemTypeOptions']
            ))
            ->add('continue', 'submit')
            ->add('update', 'submit')
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
        $resolver->setDefaults(array(
            'basketItemTypeDataClass' => null,
            'data_class' => null,
            'basketItemTypeOptions' => []
        ));
    }
}