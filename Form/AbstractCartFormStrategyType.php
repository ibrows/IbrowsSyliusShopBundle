<?php

namespace Ibrows\SyliusShopBundle\Form;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartDefaultOptionStrategyInterface;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\FormBuilderInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartFormStrategyInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;

abstract class AbstractCartFormStrategyType extends AbstractType
{
    /**
     * @var CartManager
     */
    protected $cartManager = array();

    /**
     * @param CartManager $cartManager
     */
    public function __construct(CartManager $cartManager)
    {
        $this->cartManager = $cartManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $strategies = $this->getStrategies();

        $choices = array();
        $default = null;
        foreach ($strategies as $strategy) {
            $choices[$strategy->getServiceId()] = $strategy;
            if ($strategy instanceof CartDefaultOptionStrategyInterface && $strategy->isDefault()) {
                $default = $strategy->getServiceId();
            }
        }

        $strategyOptions = array(
            'choice_list' => new ArrayChoiceList(
                $choices,
                function ($val) {
                    if(is_null($val)){
                        return null;
                    }

                    if (!is_string($val)) {
                        return $val->getServiceId();
                    }

                    return $val;
                }
            ),
            'multiple' => false,
            'expanded' => true
        );

        if ($default && (!isset($options['data']) or $options['data'] == null)) {
            $strategyOptions['data'] = $default;
        }

        $builder->add('strategyServiceId', 'choice', $strategyOptions);

        foreach ($strategies as $strategy) {
            $builder->add($strategy->getName(), get_class($strategy));
        }
    }

    /**
     * @return CartFormStrategyInterface[]
     */
    abstract protected function getStrategies();
}