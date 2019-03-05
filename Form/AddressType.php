<?php

namespace Ibrows\SyliusShopBundle\Form;

use Ibrows\SyliusShopBundle\Entity\Address;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AddressType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $countryOptions = [];
        if (isset($options['choices']) && $options['choices']) {
            $countryOptions['choices'] = $options['choices'];
            $countryOptions['choice_loader'] = null;
        }
        if (isset($options['preferred_choices']) && $options['preferred_choices']) {
            $countryOptions['preferred_choices'] = $options['preferred_choices'];
        }

        if (isset($options['titleChoices']) && $options['titleChoices']) {
            $titleChoices = $options['titleChoices'];
        }

        $builder
            ->add('title', ChoiceType::class, [
                'choices' => $titleChoices,
            ])
            ->add('firstname')
            ->add('lastname')
            ->add('company')
            ->add('street')
            ->add('zip')
            ->add('city')
            ->add('country', CountryType::class, $countryOptions)
            ->add('phone')
            ->add('email', 'email')
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ibr_sylius_address';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver); // TODO: Change the autogenerated stub

        $resolver->setDefaults([
            'data_class' => Address::class,
            'choices' => [],
            'preferred_choices' => [],
            'titleChoices' => array_flip([
                'TITLE_WOMAN'   => 'Frau',
                'TITLE_MAN'     => 'Herr',
                'TITLE_COMPANY' => 'Firma',
            ])
        ] );
    }
}
