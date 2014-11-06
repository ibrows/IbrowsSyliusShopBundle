<?php
/**
 * Created by PhpStorm.
 * User: Faebeee
 * Date: 06.11.14
 * Time: 14:35
 */

namespace Ibrows\SyliusShopBundle\Repository;


use Doctrine\ORM\EntityRepository;

class CartItemRepository extends EntityRepository
{
    /**
     * @return mixed
     */
    public function findByMostBought($limit=50)
    {
        $qb = $this->createQueryBuilder('i');
        $qb->addSelect('SUM(i.quantity) quantity');
        $qb->leftJoin('i.product', 'product');
        $qb->where('product.enabled = :enabled');
        $qb->setParameter('enabled', true);
        $qb->orderBy('quantity', 'DESC');
        $qb->groupBy('i.product');
        $qb->setMaxResults($limit);
        $result = $qb->getQuery()->getScalarResult();
        return $result;
    }
} 