<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ibrows\SyliusShopBundle\Model\Product\ProductInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\VoucherInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_voucher")
 * @ORM\InheritanceType("JOINED")
 */
class Voucher extends AbstractVoucher implements VoucherInterface, ProductInterface
{
    /**
     * @var float
     * @ORM\Column(type="decimal")
     */
    protected $value;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", name="payed_at", nullable=true)
     */
    protected $payedAt;

    /**
     * @return string
     */
    public function __toString()
    {
        return '#' . $this->getId() . ' Voucher "' . $this->getCode() . '" (' . $this->getValue() . ') | Status: ' . ($this->isPayed() ? 'payed' : 'unpayed');
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float $value
     * @return Voucher
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->getValue();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Voucher';
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * Get stock keeping unit.
     *
     * @return mixed
     */
    public function getSku()
    {
        return 'Voucher';
    }

    /**
     * Get inventory displayed name.
     *
     * @return string
     */
    public function getInventoryName()
    {
        return 'Voucher';
    }

    /**
     * Simply checks if there any stock available.
     * It should also return true for items available on demand.
     *
     * @return Boolean
     */
    public function isInStock()
    {
        return true;
    }

    /**
     * Is stockable available on demand?
     *
     * @return Boolean
     */
    public function isAvailableOnDemand()
    {
        return true;
    }

    /**
     * Get stock on hand.
     *
     * @return integer
     */
    public function getOnHand()
    {
        return 0;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return parent::isValid() && $this->isPayed() && $this->getValue() > 0;
    }

    /**
     * Set stock on hand.
     *
     * @param integer $onHand
     * @throws \Exception
     */
    public function setOnHand($onHand)
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @return \DateTime
     */
    public function getPayedAt()
    {
        return $this->payedAt;
    }

    /**
     * @param \DateTime $payedAt
     * @return Voucher
     */
    public function setPayedAt(\DateTime $payedAt = null)
    {
        $this->payedAt = $payedAt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPayed()
    {
        return $this->getPayedAt() !== null;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return Voucher
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }
}