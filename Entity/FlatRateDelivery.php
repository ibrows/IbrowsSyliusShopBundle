<?php

namespace Ibrows\SyliusShopBundle\Entity;
use Ibrows\SyliusShopBundle\Model\Delivery\DeliveryOptionInterface;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_delivery_option_free")
 */
class FlatRateDelivery extends AdditionalCartItem implements DeliveryOptionInterface
{
    /**
     * Total value.
     * @ORM\Column(type="decimal", scale=2, precision=11)
     * @var float
     */
    protected $minTotal;

    /**
     * @return float
     */
    public function getMinTotal()
    {
        return $this->minTotal;
    }

    /**
     * @param $minTotal
     * @return FlatRateDelivery
     */
    public function setMinTotal($minTotal)
    {
        $this->minTotal = $minTotal;
        return $this;
    }

}
