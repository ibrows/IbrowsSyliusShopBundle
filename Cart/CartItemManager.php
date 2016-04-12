<?php
/**
 * Created by PhpStorm.
 * User: Faebeee
 * Date: 24.07.14
 * Time: 16:20.
 */

namespace Ibrows\SyliusShopBundle\Cart;

use Doctrine\ORM\EntityManager;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;
use Ibrows\SyliusShopBundle\Model\Product\ProductInterface;
use Ibrows\SyliusShopBundle\Repository\CartItemRepository;

class CartItemManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $productClass;

    /**
     * @var string
     */
    protected $skuField;

    /**
     * @var string
     */
    protected $cartClass;

    /**
     * @var CartItemRepository
     */
    protected $cartItemRepo;

    /**
     * @param EntityManager $em
     * @param string        $productClass
     * @param $cartClass
     * @param string             $skuProperty
     * @param CartItemRepository $cartItemRepo
     */
    public function __construct(EntityManager $em, $productClass, $cartClass, $skuProperty = 'sku', CartItemRepository $cartItemRepo = null)
    {
        $this->em = $em;
        $this->productClass = $productClass;
        $this->cartClass = $cartClass;
        $this->skuField = $skuProperty;
        $this->cartItemRepo = $cartItemRepo;
    }

    /**
     * @param CartItemInterface $cartItemInterface
     *
     * @return array
     */
    public function getBoughtWith(CartItemInterface $cartItemInterface)
    {
        $cart = $cartItemInterface->getCart();
        $product = $cartItemInterface->getProduct();

        return $this->getBoughtProducts($product, $cart);
    }

    /**
     * @param ProductInterface $product
     * @param CartInterface    $cart
     *
     * @return array
     */
    public function getBoughtWithProduct(ProductInterface $product, CartInterface $cart = null)
    {
        if ($cart == null) {
            $cart = $this->cartClass;
        }

        $cartRepo = $this->getRepositoryForClass($cart);

        $qb = $cartRepo->createQueryBuilder('c');
        $qb->leftJoin('c.items', 'i');
        $qb->where('i.product = :product ');
        $qb->setParameter('product', $product);

        /** @var CartInterface[] $carts */
        $carts = $qb->getQuery()->execute();

        $articles = array();
        foreach ($carts as $_cart) {
            $_articles = $this->getBoughtProducts($_cart);
            foreach ($_articles as $article) {
                $articles[$article->getId()] = $article;
            }
        }

        return $articles;
    }

    /**
     * @param CartInterface $cart
     *
     * @return ProductInterface[]
     */
    public function getBoughtProducts(CartInterface $cart)
    {
        $skus = array();

        foreach ($cart->getItems() as $item) {
            $skus[$item->getProduct()->getSku()] = $item->getProduct()->getSku();
        }
        $_sku = array();
        foreach ($skus as $sku) {
            $_sku[] = $sku;
        }
        if (count($_sku) > 0) {
            return $this->getProductsBySku($_sku);
        }

        return array();
    }

    /**
     * @param CartInterface[] $carts
     * @param int             $limit
     *
     * @return ProductInterface[]
     */
    public function getMostBoughtProductsFromCarts(array $carts, $limit = null)
    {
        $skus = array();
        foreach ($carts as $cart) {
            foreach ($cart->getItems() as $item) {
                if (!isset($skus[$item->getProduct()->getSku()])) {
                    $skus[$item->getProduct()->getSku()] = 0;
                }
                $skus[$item->getProduct()->getSku()] += $item->getQuantity();
            }
        }

        if (count($skus) > 0) {
            arsort($skus);
            $articleIds = array();
            foreach ($skus as $key => $quantity) {
                $articleIds[$quantity] = $key;
            }

            return $this->getProductsBySku($articleIds, $limit);
        }

        return array();
    }

    /**
     * @param int $limit
     *
     * @return ProductInterface[]
     */
    public function getMostBoughtProducts($limit = 50)
    {
        return $this->cartItemRepo->findByMostBoughtProducts($limit);
    }

    /**
     * @param array $skus
     * @param int   $limit
     *
     * @return ProductInterface[]
     */
    protected function getProductsBySku(array $skus, $limit = null)
    {
        if (!$skus) {
            return array();
        }

        $repo = $this->em->getRepository($this->productClass);
        $qb = $repo->createQueryBuilder('p');
        $qb->where('p.'.$this->skuField.' IN (:sku)');
        $qb->setParameter('sku', $skus);

        if ($limit != null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * @param $class
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepositoryForClass($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        return $this->em->getRepository($class);
    }
}
