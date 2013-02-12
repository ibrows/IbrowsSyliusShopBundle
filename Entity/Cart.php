<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Sylius\Bundle\CartBundle\Model\CartInterface;

use Sylius\Bundle\CartBundle\Model\CartItemInterface;

use Doctrine\Common\Collections\Collection;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Bundle\CartBundle\Entity\Cart as BaseCart;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_cart")
 */
class Cart  extends BaseCart
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Items in cart.
     * @ORM\OneToMany(targetEntity="CartItem", mappedBy="cart", cascade="all", orphanRemoval=true)
     * @ORM\JoinColumn(name="typ_id", referencedColumnName="id")
     * @var Collection
     */
    protected $items;

    public function refreshCart()
    {
        $this->calculateTotal();
        $this->setTotalItems($this->countItems());
        return $this;
    }

    public function getItems(){
        return $this->items;
    }

    public function addItem(CartItemInterface $item){
        parent::addItem($item);
        $this->refreshCart();
    }

    public function removeItem(CartItemInterface $item){
        parent::removeItem($item);
        $this->refreshCart();
    }

    public function setItems(Collection $items){
        foreach($this->items as $item){
            $this->removeItem($item);
        }
        foreach($items as $item){
            $this->addItem($item);
        }
    }

}