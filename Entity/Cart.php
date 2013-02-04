<?php

namespace Ibrows\SyliusShopBundle\Entity;

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
     * @ORM\OneToMany(targetEntity="CartItem", mappedBy="cart", cascade="all")
     * @ORM\JoinColumn(name="typ_id", referencedColumnName="id")
     * @var Collection
     */
    protected $items;
}