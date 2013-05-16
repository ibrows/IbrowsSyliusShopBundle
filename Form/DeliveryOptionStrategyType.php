<?php

namespace Ibrows\SyliusShopBundle\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormBuilderInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartDeliveryOptionStrategyInterface;

class DeliveryOptionStrategyType extends AbstractType
{
    /**
     * @var CartDeliveryOptionStrategyInterface[]
     */
    protected $strategies = array();

    /**
     * @param CartDeliveryOptionStrategyInterface[] $strategies
     */
    public function __construct(array $strategies)
    {
        $this->strategies = new ArrayCollection();
        foreach($strategies as $strategy){
            $this->addStrategy($strategy);
        }
    }

    /**
     * @param CartDeliveryOptionStrategyInterface $strategy
     */
    public function addStrategy(CartDeliveryOptionStrategyInterface $strategy)
    {
        $this->strategies->add($strategy);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $strategyChoices = array();
        foreach($this->strategies as $strategy){
            $strategyChoices[$strategy->getServiceId()] = $strategy->getServiceId();
        }

        $builder->add('deliveryOptionStrategyServiceId', 'choice', array(
            'choices' => $strategyChoices
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ibr_sylius_deliverystrategy';
    }
}