<?php

namespace Ibrows\SyliusShopBundle\Form;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartDefaultOptionStrategyInterface;
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
        $default = null;
        foreach($strategies as $strategy){
            $choices[$strategy->getServiceId()] = $strategy;
            if($strategy instanceof CartDefaultOptionStrategyInterface && $strategy->isDefault()){
                $default = $strategy->getServiceId();
            }
        }

        $strategyOptions = array(
            'choices' => $choices,
            'multiple' => false,
            'expanded' => true
        );

        if($default && (!isset($options['data']) OR $options['data'] == null)){
            $strategyOptions['data'] = $default;
        }

        $builder->add('strategyServiceId', 'choice', $strategyOptions);

        foreach($strategies as $strategy){
            $builder->add($strategy->getName(), $strategy);
        }
    }

    /**
     * @return CartFormStrategyInterface[]
     */
    abstract protected function getStrategies();
}