<?php

namespace Ibrows\SyliusShopBundle\Form;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Symfony\Component\Form\FormBuilderInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartFormStrategyInterface;

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
        foreach($strategies as $strategy){
            $choices[$strategy->getServiceId()] = $strategy;
        }

        $builder->add('strategyServiceId', 'choice', array(
            'choices' => $choices,
            'multiple' => false,
            'expanded' => true
        ));

        foreach($strategies as $strategy){
            $builder->add($strategy->getName(), $strategy);
        }
    }

    /**
     * @return CartFormStrategyInterface[]
     */
    abstract protected function getStrategies();
}