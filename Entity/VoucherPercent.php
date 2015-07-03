<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ibrows\SyliusShopBundle\Model\Voucher\VoucherPercentInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_voucher_percent")
 * @ORM\InheritanceType("JOINED")
 */
class VoucherPercent extends AbstractVoucher implements VoucherPercentInterface
{
    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @Assert\Range(min=1, max=100)
     */
    protected $percent;

    /**
     * @return integer
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * @param integer $percent
     * @return VoucherPercentInterface
     */
    public function setPercent($percent)
    {
        $this->percent = $percent;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '#' . $this->getId() . ' Voucher "' . $this->getCode() . '" (' . $this->getPercent() . '%) | Quantity: ' . $this->getQuantity();
    }
}