<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Ibrows\SyliusShopBundle\Cart\CartInterface;
use Sylius\Bundle\CartBundle\Model\CartItemInterface;

use Doctrine\Common\Collections\Collection;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Bundle\CartBundle\Entity\Cart as BaseCart;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_cart")
 */
class Cart extends BaseCart implements CartInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="CartItem", mappedBy="cart", cascade="all", orphanRemoval=true)
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id")
     */
    protected $items;

    /**
     * @return Cart
     */
    public function refreshCart(){
        $this->calculateTotal();
        $this->setTotalItems($this->countItems());
        return $this;
    }

    /**
     * @return CartItemInterface[]|Collection
     */
    public function getItems(){
        return $this->items;
    }

    /**
     * @param CartItemInterface $item
     * @return Cart
     */
    public function addItem(CartItemInterface $item){
        parent::addItem($item);
        $this->refreshCart();

        return $this;
    }

    /**
     * @param CartItemInterface $item
     * @return Cart
     */
    public function removeItem(CartItemInterface $item){
        parent::removeItem($item);
        $this->refreshCart();

        return $this;
    }

    /**
     * @param Collection|CartItemInterface[] $items
     * @return Cart
     */
    public function setItems(Collection $items){
        foreach($this->items as $item){
            $this->removeItem($item);
        }

        foreach($items as $item){
            $this->addItem($item);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Cart
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }
}