<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy;

use Ibrows\SyliusShopBundle\Cart\CartManager;
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
    protected $defaultTranslationDomain = "messages";

    /**
     * @param array $parameters
     * @param string $prefix
     * @param string $suffix
     * @return array
     */
    protected function transformToTranslationKeys(array $parameters, $prefix = '%', $suffix = '%')
    {
        $newParameters = array();
        foreach($parameters as $key => $value){
            $newParameters[$prefix.$key.$suffix] = $value;
        }
        return $newParameters;
    }

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
     * @return string
     */
    public function getTranslationKey(CartInterface $cart)
    {
        return $this->getServiceId();
    }

    /**
     * @param CartInterface $cart
     * @return array
     */
    public function getTranslationParameters(CartInterface $cart)
    {
        return array();
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return bool
     */
    public function isPossible(CartInterface $cart, CartManager $cartManager)
    {
        return true;
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