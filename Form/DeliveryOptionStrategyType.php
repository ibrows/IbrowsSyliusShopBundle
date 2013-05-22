<?php

namespace Ibrows\SyliusShopBundle\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Ibrows\SyliusShopBundle\Cart\CartManager;
use Symfony\Component\Form\FormBuilderInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartDeliveryOptionStrategyInterface;

class DeliveryOptionStrategyType extends AbstractType
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
        $strategyChoices = array();
        $cart = $this->cartManager->getCart();
        foreach($this->cartManager->getDeliveryOptionStrategies() as $strategy){
            $costs = 0.0;
            foreach($strategy->compute($cart, $this->cartManager) as $item){
                $costs += $item->getPrice();
            }
            $strategyChoices[$strategy->getServiceId()] = $strategy->getServiceId().' ('. $costs .')';
        }

        $builder->add('deliveryOptionStrategyServiceId', 'choice', array(
            'choices' => $strategyChoices,
            'expanded' => true
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