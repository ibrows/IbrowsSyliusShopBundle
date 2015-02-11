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
     * @var \DateTime
     * @ORM\Column(type="datetime", name="valid_from", nullable=true)
     */
    protected $validFrom;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", name="valid_to", nullable=true)
     */
    protected $validTo;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $quantity;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @Assert\Range(min=1, max=100)
     */
    protected $percent;

    /**
     * @return \DateTime
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * @param \DateTime $validFrom
     * @return VoucherPercent
     */
    public function setValidFrom(\DateTime $validFrom = null)
    {
        $this->validFrom = $validFrom;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * @param \DateTime $validTo
     * @return VoucherPercent
     */
    public function setValidTo(\DateTime $validTo = null)
    {
        $this->validTo = $validTo;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     * @return VoucherPercent
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

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
     * @return bool
     */
    public function hasQuantity()
    {
        return !is_null($this->getQuantity());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '#' . $this->getId() . ' Voucher "' . $this->getCode() . '" (' . $this->getPercent() . '%) | Quantity: ' . $this->getQuantity();
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $now = new \DateTime();

        if (($from = $this->getValidFrom()) && $from > $now) {
            return false;
        }

        if (($to = $this->getValidTo()) && $to < $now) {
            return false;
        }

        if (!$this->hasQuantity()) {
            return true;
        }

        return $this->getQuantity() > 0;
    }
}