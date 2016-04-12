<?php

namespace Ibrows\SyliusShopBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class AuthType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'email', array(
            'constraints' => array(
                new NotBlank(array('groups' => array('sylius_wizard_auth'))),
                new Email(array('groups' => array('sylius_wizard_auth'))),
            ),
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ibr_sylius_auth';
    }
}
