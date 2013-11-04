<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Ibrows\SyliusShopBundle\Model\Product\ProductInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_voucher")
 * @ORM\InheritanceType("JOINED")
 */
class Voucher implements ProductInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $code;

    /**
     * @var float
     * @ORM\Column(type="float")
     */
    protected $value;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", name="created_at")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", name="payed_at", nullable=true)
     */
    protected $payedAt;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

    public function __toString()
    {
        return '#'. $this->getId() .' Voucher "'. $this->getCode() .'" ('. $this->getValue() .') | Status: '. ($this->isPayed() ? 'payed' : 'unpayed');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     * @return Voucher
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return Voucher
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
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
}