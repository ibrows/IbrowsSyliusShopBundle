<?php

namespace Ibrows\SyliusShopBundle\Form;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

class AddressType extends AbstractType
{
    /**
     * @var array
     */
    protected $countryChoices;

    /**
     * @var array
     */
    protected $preferredCountryChoices;

    /**
     * @var array
     */
    protected $titleChoices;

    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @param array  $countryChoices
     * @param array  $preferredCountryChoices
     * @param array  $titleChoices
     * @param string $dataClass
     */
    public function __construct(
        array $countryChoices = array(),
        array $preferredCountryChoices = array(),
        array $titleChoices = array(),
        $dataClass = null
    ) {
        $this->countryChoices = $countryChoices;
        $this->preferredCountryChoices = $preferredCountryChoices;
        $this->titleChoices = $titleChoices;
        $this->dataClass = $dataClass;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $countryOptions = array();
        if ($this->countryChoices) {
            $countryOptions['choices'] = $this->countryChoices;
        }
        if ($this->preferredCountryChoices) {
            $countryOptions['preferred_choices'] = $this->preferredCountryChoices;
        }

        $builder
            ->add('title', 'choice', array(
                'choices' => $this->titleChoices,
            ))
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
        if ($this->dataClass) {
            $resolver->setDefaults(array(
                'data_class' => $this->dataClass,
            ));
        }
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
     *
     * @return AddressType
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
     *
     * @return AddressType
     */
    public function setCountryChoices($countryChoices)
    {
        $this->countryChoices = $countryChoices;

        return $this;
    }

    /**
     * @return array
     */
    public function getTitleChoices()
    {
        return $this->titleChoices;
    }

    /**
     * @param array $titleChoices
     *
     * @return AddressType
     */
    public function setTitleChoices($titleChoices)
    {
        $this->titleChoices = $titleChoices;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ibr_sylius_address';
    }
}
