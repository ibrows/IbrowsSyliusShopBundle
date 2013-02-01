<?php


namespace Ibrows\SyliusShopBundle\Entity;

use Sylius\Bundle\InventoryBundle\Entity\InventoryUnit as BaseInventoryUnit;
use Sylius\Bundle\SalesBundle\Model\OrderInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_inventory_unit")
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
     * Order.
     *
     * @var OrderInterface
     */
    private $order;

    public function __construct()
    {
        parent::__construct();

        $this->shippingState = ShipmentItemInterface::STATE_READY;
    }

    /**
     * Get order.
     *
     * @return OrderInterface
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set order.
     *
     * @param OrderInterface $order
     */
    public function setOrder(OrderInterface $order = null)
    {
        $this->order = $order;
    }

}
