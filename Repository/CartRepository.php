<?php

namespace Ibrows\SyliusShopBundle\Repository;

use Doctrine\DBAL\LockMode;
use Sylius\Bundle\CartBundle\Entity\CartRepository as BaseCartRepository;

use Doctrine\ORM\EntityRepository;

class CartRepository extends BaseCartRepository
{
    /**
     * Bugfix for intval-Arry-Bug
     *
     * @param mixed $id
     * @param $lockMode
     * @param null $lockVersion
     * @return mixed|null|object
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        return $this->_em->find($this->_entityName, $id, $lockMode, $lockVersion);
    }
}