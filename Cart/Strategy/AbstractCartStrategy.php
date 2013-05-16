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
}