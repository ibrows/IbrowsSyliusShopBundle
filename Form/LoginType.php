<?php

namespace Ibrows\SyliusShopBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

class LoginType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('_username')
            ->add('_password', 'password')
            ->add('_remember_me', 'checkbox', array('required' => false))
            ->add('_failure_path', 'hidden')
            ->add('_target_path', 'hidden')
            ->add('_csrf_token', 'hidden');
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return '';
    }
}
