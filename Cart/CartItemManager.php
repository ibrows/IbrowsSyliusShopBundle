<?php
/**
 * Created by PhpStorm.
 * User: Faebeee
 * Date: 24.07.14
 * Time: 16:20
 */

namespace Ibrows\SyliusShopBundle\Cart;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Ibrows\SyliusShopBundle\Entity\CartItem;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;
use Ibrows\SyliusShopBundle\Repository\CartItemRepository;
use Ibrows\SyliusShopBundle\Repository\ProductRepository;

class CartItemManager {
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

    protected $cartClass;

    /**
     * @var CartItemRepository
     */
    protected $cartItemRepo;

    /**
     * @param EntityManager $em
     * @param string $class
     * @param $cartClass
     * @param string $skuProperty
     * @param null $cartItemClass
     */
    function __construct($em, $class, $cartClass, $skuProperty='sku', CartItemRepository $cartItemRepo=null)
    {
        $this->em = $em;
        $this->productClass = $class;
        $this->cartClass = $cartClass;
        $this->skuField = $skuProperty;
        $this->cartItemRepo = $cartItemRepo;

    }

    /**
     * @param CartItemInterface $cartItemInterface
     * @return array
     */
    public function getBoughtWith ( CartItemInterface $cartItemInterface) {
        $cart = $cartItemInterface->getCart();
        $product = $cartItemInterface->getProduct();

        return $this->getBoughtProducts($product, $cart);
    }

    public function getBoughtWithProduct($product, $cart=null){
        if($cart == null){
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
        foreach($carts as $_cart){
            $_articles = $this->getBoughtProducts($_cart);
            foreach($_articles as $article){
                $articles[$article->getId()] = $article;
            }
        }
        return $articles;
    }

    /**
     * @param CartInterface $cart
     * @return array
     */
    public function getBoughtProducts( CartInterface $cart ) {
        $skus = array();

        foreach($cart->getItems() as $item){
            $skus[$item->getProduct()->getSku()] = $item->getProduct()->getSku();
        }
        $_sku = array();
        foreach($skus as $sku){
            $_sku[] = $sku;
        }
        if(count($_sku) > 0){
            return $this->getProductsBySku($_sku);
        }
        return array();
    }

    /**
     * @param $carts
     * @param int $limit
     * @return array
     */
    public function getMostBoughtProducts($carts, $limit = null){
        $skus = array();
        foreach($carts as $cart) {
            foreach($cart->getItems() as $item){
                if ( !isset($skus[$item->getProduct()->getSku()]) ) {
                    $skus[$item->getProduct()->getSku()] = 0;
                }
                $skus[$item->getProduct()->getSku()] += $item->getQuantity();
            }
        }
        if ( count($skus) > 0 ){
            arsort($skus);
            $articleIds = array();
            foreach($skus as $key => $quantity){
                $articleIds[$quantity] = $key;
            }

            return $this->getProductsBySku($articleIds, $limit);
        }

        return array();
    }

    /**
     * @param array $skus
     * @param int $limit
     * @return array
     */
    protected function getProductsBySku($skus, $limit=null){
        $repo = $this->em->getRepository($this->productClass);
        $qb = $repo->createQueryBuilder('p');
        $qb->where('p.'.$this->skuField.' IN (:sku)');
        $qb->setParameter('sku', $skus);

        if($limit != null ){
            $qb->setMaxResults($limit);
        }
        return $qb->getQuery()->execute();
    }

    /**
     * @param $class
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepositoryForClass($class){
        if(is_object($class)){
            $class = get_class($class);
        }
        return $this->em->getRepository($class);
    }

    /**
     *
     */
    public function getMostBoughtArticles($limit=50)
    {
        return $this->cartItemRepo->findByMostBought($limit);
    }


} 