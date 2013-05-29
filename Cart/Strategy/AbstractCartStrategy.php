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
     * @return AdditionalCartItemInterface
     */
    protected function createAdditionalCartItem()
    {
        $className = $this->additionalCartItemRepo->getClassName();

        /* @var AdditionalCartItemInterface $item */
        $item = new $className();
        $item->setStrategyIdentifier($this->getServiceId());

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