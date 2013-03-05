<?php

namespace Ibrows\SyliusShopBundle\Entity;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

use Sylius\Bundle\InventoryBundle\Entity\InventoryUnit as BaseInventoryUnit;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_inventory_unit")
 */
class InventoryUnit extends BaseInventoryUnit
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Cart.
     *
     * @var CartInterface
     */
    protected $cart;

    public function __construct()
    {
        parent::__construct();

        $this->shippingState = ShipmentItemInterface::STATE_READY;
    }

    public function getCart()
    {
        return $this->cart;
    }

    public function setCart(CartInterface $cart)
    {
        $this->cart = $cart;
        return $this;
    }

}
