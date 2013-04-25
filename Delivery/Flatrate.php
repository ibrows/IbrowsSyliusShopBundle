<?php
namespace Ibrows\SyliusShopBundle\Delivery;
use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;

use Ibrows\SyliusShopBundle\Entity\FlatRateDelivery;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;


use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

class Flatrate
{

    protected $em;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->em = $doctrine->getManager();
    }

    public function getType(){
        return 'delivery';
    }

    public function isPossible(CartInterface $cart)
    {
        return true;
    }

    protected function getOptions(){
        $opts = array();
        $opt = new \stdClass();
        $opt->price = 10;
        $opt->mintotal = 0;
        $opts[] = $opt;
        $opt = new \stdClass();
        $opt->price = 0;
        $opt->mintotal = 1000;
        $opts[] = $opt;
        return $opts;
    }

    public function getPossibleAdditionalCartItems(CartInterface $cart)
    {
        $items = array();
        $total = $cart->getTotal();
        $total = 1111;
        foreach($this->getOptions() as $opt){
            if($opt->mintotal <= $total){
                $item = new FlatRateDelivery();
                $item->setPrice($opt->price);
                $item->setText('Lieferpauschale');
                $item->setMinTotal($opt->mintotal);
                $items[] = $item;
            }
        }
        return $items;
    }

    public function check(CartInterface $cart, AdditionalCartItemInterface $selected)
    {

    }

}
