<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use spec\Sylius\Bundle\InventoryBundle\Model\InventoryUnit as BaseInventoryUnit;
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
     * @var CartInterface
     */
    protected $cart;

    public function __construct()
    {
        parent::__construct();
        $this->shippingState = ShipmentItemInterface::STATE_READY;
    }

    /**
     * @return CartInterface
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param CartInterface $cart
     * @return InventoryUnit
     */
    public function setCart(CartInterface $cart)
    {
        $this->cart = $cart;
        return $this;
    }
}
