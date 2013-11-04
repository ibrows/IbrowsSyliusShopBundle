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
     * @var float
     * @ORM\Column(type="float")
     * @Assert\Range(min=0.01, max=0.99)
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
     * @return bool
     */
    public function isValid()
    {
        $now = new \DateTime();
        return $now > $this->getValidFrom() && $now < $this->getValidTo() && $this->getQuantity() > 0;
    }

    /**
     * @return float
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * @param float $percent
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
    public static function getPrefix()
    {
        return 'p';
    }

    /**
     * @param string $code
     * @return bool
     */
    public static function acceptCode($code)
    {
        return substr($code, 0, 1) == self::getPrefix();
    }
}