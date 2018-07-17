<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Delivery;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SelfpickupDeliveryCartStrategy extends AbstractDeliveryCartStrategy
{
    /**
     * @var array
     */
    protected $stores;

    /**
     * @var string
     */
    protected $defaultStore;

    /**
     * @param array $stores
     * @param string $defaultStore
     */
    public function __construct(array $stores = array(), $defaultStore = null)
    {
        $this->stores = $stores;
        $this->setDefaultStore($defaultStore);
    }

    /**
     * @return array
     */
    public function getStores()
    {
        return $this->stores;
    }

    /**
     * @param CartInterface $cart
     * @return string
     */
    public function getStore(CartInterface $cart)
    {
        $data = $cart->getDeliveryOptionStrategyServiceData();
        if(!isset($data['store'])){
            return null;
        }
        return isset($this->stores[$data['store']]) ? $this->stores[$data['store']] : null;
    }

    /**
     * @param array $stores
     * @return SelfpickupDeliveryCartStrategy|$this
     */
    public function setStores($stores)
    {
        $this->stores = $stores;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDefaultStore()
    {
        return $this->defaultStore;
    }

    /**
     * @param $defaultStore
     * @return SelfpickupDeliveryCartStrategy|$this
     */
    public function setDefaultStore($defaultStore)
    {
        if (array_key_exists($defaultStore, $this->getStores())) {
            $this->defaultStore = $defaultStore;
        }

        return $this;
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
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return AdditionalCartItemInterface[]
     */
    public function compute(CartInterface $cart, CartManager $cartManager)
    {
        $store = null;

        if(count($this->stores) > 0){
            $data = $cart->getDeliveryOptionStrategyServiceData();

            if (!isset($data['store']) OR !isset($this->stores[$data['store']])) {
                $this->removeStrategy($cart);
                return array();
            }

            $store = $data['store'];
        }

        $costs = $this->getCostsForStore($store, $cart, $cartManager);

        if ($costs) {
            $item = $this->createAdditionalCartItem($costs);
            $item->setText($this->getItemText($store, $costs, $cart, $cartManager));
            $item->setStrategyData(
                array(
                    'store' => $store,
                    'costs' => $costs
                )
            );

            return array($item);
        }

        return array();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->stores) {
            $builder->add(
                'store',
                'choice',
                array(
                    'choices'  => $this->stores,
                    'data' => $this->defaultStore,
                    'expanded' => true
                )
            );
        }
    }

    /**
     * @param mixed $store
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return float
     */
    protected function getCostsForStore($store, CartInterface $cart, CartManager $cartManager)
    {
        return $this->getCosts($cart, $cartManager);
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return int
     */
    protected function getCosts(CartInterface $cart, CartManager $cartManager)
    {
        return 0;
    }

    /**
     * @param mixed $store
     * @param float $costs
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return string
     */
    protected function getItemText($store, $costs, CartInterface $cart, CartManager $cartManager)
    {
        return $this->getServiceId();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }

    public function getBlockPrefix()
    {
        return "";
    }
}