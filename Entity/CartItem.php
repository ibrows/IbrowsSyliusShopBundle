<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Bundle\CartBundle\Entity\CartItem as BaseCartItem;

/**
 * @ORM\Entity
 * @ORM\Table()
 */
class CartItem extends BaseCartItem
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
}