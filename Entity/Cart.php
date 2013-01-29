<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Bundle\CartBundle\Entity\Cart as BaseCart;

/**
 * @ORM\Entity
 * @ORM\Table()
 */
class Cart  extends BaseCart
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
}