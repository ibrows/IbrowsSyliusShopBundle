<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartFormStrategyInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class AbstractCartFormStrategy extends AbstractCartStrategy implements CartFormStrategyInterface
{
    /**
     * @var bool
     */
    protected $isParentVisible = true;
    
    /**
     * @var string
     */
    protected $defaultTranslationDomain = "Forms";

    /**
     * @return bool
     */
    public function isParentVisible()
    {
        return $this->isParentVisible;
    }

    /**
     * @param bool $flag
     * @return AbstractCartFormStrategy
     */
    public function setParentVisible($flag)
    {
        $this->isParentVisible = $flag;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getServiceId();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return str_replace(".", "_", $this->getServiceId());
    }

    /**
     * @param CartInterface $cart
     * @return string
     */
    public function getFullPaymentMethodName(CartInterface $cart)
    {
        return $this->getServiceId();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {

    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {

    }

    /**
     * @return string|null|FormTypeInterface
     */
    public function getParent()
    {
        return 'form';
    }
    
    /**
     * @return string
     */
    public function getDefaultTranslationDomain()
    {
        return $this->defaultTranslationDomain;
    }

    /**
     * @param string $domain
     * @return AbstractCartFormStrategy
     */
    public function setDefaultTranslationDomain($domain)
    {
        $this->defaultTranslationDomain = $domain;
        return $this;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => $this->getDefaultTranslationDomain(),
        ));
    }
}