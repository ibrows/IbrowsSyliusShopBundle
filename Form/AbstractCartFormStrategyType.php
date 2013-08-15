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
        $data = null;
        foreach($strategies as $strategy){
            $choices[$strategy->getServiceId()] = $strategy;
            if($strategy instanceof CartDefaultOptionStrategyInterface && $strategy->isDefault()){
                $data = $strategy->getServiceId();
            }
        }

        $options = array(
            'choices' => $choices,
            'multiple' => false,
            'expanded' => true
        );

        if($data){
            $options['empty_data'] = $data;
        }

        $builder->add('strategyServiceId', 'choice', $options);

        foreach($strategies as $strategy){
            $builder->add($strategy->getName(), $strategy);
        }
    }

    /**
     * @return CartFormStrategyInterface[]
     */
    abstract protected function getStrategies();
}