<?php

namespace Ibrows\SyliusShopBundle\Form;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractAddressType extends AbstractType
{
    /**
     * @var array
     */
    protected $countryChoices = array();

    /**
     * @var array
     */
    protected $preferredCountryChoices = array();

    public function __construct(array $countryChoices = array(), array $preferredCountryChoices = array())
    {
        $this->countryChoices = $countryChoices;
        $this->preferredCountryChoices = $preferredCountryChoices;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $countryOptions = array();
        if($this->countryChoices){
            $countryOptions['choices'] = $this->countryChoices;
        }
        if($this->preferredCountryChoices){
            $countryOptions['preferred_choices'] = $this->preferredCountryChoices;
        }

        $builder
            ->add('firstname')
            ->add('lastname')
            ->add('company')
            ->add('street')
            ->add('zip')
            ->add('city')
            ->add('country', 'country', $countryOptions)
            ->add('phone')
            ->add('email', 'email')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
    }

    /**
     * @return array
     */
    public function getPreferredCountryChoices()
    {
        return $this->preferredCountryChoices;
    }

    /**
     * @param array $preferredCountryChoices
     * @return AbstractAddressType
     */
    public function setPreferredCountryChoices($preferredCountryChoices)
    {
        $this->preferredCountryChoices = $preferredCountryChoices;

        return $this;
    }

    /**
     * @return array
     */
    public function getCountryChoices()
    {
        return $this->countryChoices;
    }

    /**
     * @param array $countryChoices
     * @return AbstractAddressType
     */
    public function setCountryChoices($countryChoices)
    {
        $this->countryChoices = $countryChoices;
        return $this;
    }
}