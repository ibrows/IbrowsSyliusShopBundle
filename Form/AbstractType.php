<?php

namespace Ibrows\SyliusShopBundle\Form;

use Symfony\Component\Form\AbstractType as BaseType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractType extends BaseType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'translation_domain' => 'IbrowsSyliusShopBundle',
        ));
    }
}
