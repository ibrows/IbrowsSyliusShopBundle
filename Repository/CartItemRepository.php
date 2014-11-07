<?php
/**
 * Created by PhpStorm.
 * User: Faebeee
 * Date: 06.11.14
 * Time: 14:35
 */

namespace Ibrows\SyliusShopBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;
use Ibrows\SyliusShopBundle\Model\Product\ProductInterface;

class CartItemRepository extends EntityRepository
{
    /**
     * @param int $limit
     * @return ProductInterface[]
     */
    public function findByMostBoughtProducts($limit = 50)
    {
        $qb = $this->createQueryBuilder('item');

        $qb->addSelect('SUM(item.quantity) as itemQuantity');
        $qb->addSelect('product');

        $qb->leftJoin('item.product', 'product');
        $qb->leftJoin('item.cart', 'cart');

        $qb->where('product.enabled = :enabled');
        $qb->setParameter('enabled', true);

        $qb->andWhere($qb->expr()->isNotNull('cart.confirmedAt'));

        $qb->orderBy('itemQuantity', 'DESC');
        $qb->groupBy('product.id');

        $qb->setMaxResults($limit);

        $rows = $qb->getQuery()->getResult();
        $products = array();

        foreach ($rows as $row) {
            if (!isset($row[0])) {
                continue;
            }
            /** @var CartItemInterface $item */
            $item = $row[0];
            $products[] = $item->getProduct();
        }

        return $products;
    }
} 