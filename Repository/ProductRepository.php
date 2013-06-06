<?php

namespace Ibrows\SyliusShopBundle\Repository;



use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\LockMode;

class ProductRepository extends EntityRepository
{
//overwrite sylius
    public function find($id, $lockMode = LockMode::NONE, $lockVersion = null)
    {
        return $this->_em->find($this->_entityName, $id, $lockMode, $lockVersion);
    }
}