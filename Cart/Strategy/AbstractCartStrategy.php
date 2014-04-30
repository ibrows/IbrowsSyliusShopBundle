<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy;

use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartStrategyInterface;
use Doctrine\Common\Persistence\ObjectRepository;

abstract class AbstractCartStrategy implements CartStrategyInterface
{
    /**
     * @var ObjectRepository
     */
    protected $additionalCartItemRepo;

    /**
     * @var string
     */
    protected $serviceId;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @var bool
     */
    protected $taxincl = false;

    /**
     * @return array
     */
    public function __sleep()
    {
        return array('serviceId');
    }

    /**
     * @param ObjectRepository $repo
     * @return ObjectRepository
     */
    public function setAdditionalCartItemRepo(ObjectRepository $repo)
    {
        $this->additionalCartItemRepo = $repo;
        return $this;
    }

    /**
     * @return ObjectRepository
     */
    public function getAdditionalCartItemRepo()
    {
        return $this->additionalCartItemRepo;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     * @return AbstractCartStrategy
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @param string $serviceId
     * @return AbstractCartStrategy
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;
        return $this;
    }


    /**
     * @return boolean
     */
    public function getTaxincl()
    {
        return $this->taxincl;
    }

    /**
     * @param boolean $taxincl
     * @return AbstractCartStrategy
     */
    public function setTaxincl($taxincl)
    {
        $this->taxincl = $taxincl;
        return $this;
    }

    /**
     * @param int $price
     * @param string $text
     * @param array $data
     * @return AdditionalCartItemInterface
     */
    protected function createAdditionalCartItem($price = 0, $text = null, array $data = array())
    {
        $className = $this->additionalCartItemRepo->getClassName();

        /* @var AdditionalCartItemInterface $item */
        $item = new $className();
        $item->setStrategyIdentifier($this->getServiceId());
        if($this->getTaxincl())
            $item->setPriceWithTax($price);
        else
            $item->setPrice($price);
        $item->setStrategyData($data);
        $item->setText($text?:$this->getServiceId());
        return $item;
    }

    /**
     * @param array $steps
     * @param mixed $step
     * @param bool $firstAsZero
     * @return mixed
     */
    protected function getStepCosts(array $steps, $step, $firstAsZero = false)
    {
        $firstStep = reset($steps);
        $costs = !$firstAsZero && $firstStep ? $firstStep : 0;
        foreach($steps as $minTotal => $stepCosts){
            if($step < $minTotal){
                break;
            }
            $costs = $stepCosts;
        }
        return $costs;
    }
}