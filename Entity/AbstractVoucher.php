<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ibrows\SyliusShopBundle\Model\Voucher\BaseVoucherInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\VoucherCodeInterface;
use Ibrows\SyliusShopBundle\Validator\Constraints as IbrowsShopAssert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\MappedSuperclass
 * @IbrowsShopAssert\IsUniqueVoucher(errorPath="code")
 */
abstract class AbstractVoucher implements BaseVoucherInterface
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
     * @ORM\Column(type="string", unique=true)
     */
    protected $code;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", name="created_at")
     */
    protected $createdAt;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=30, precision=65, nullable=true)
     */
    protected $minimumOrderValue;

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
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $currency;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param VoucherCodeInterface $voucherCode
     * @return bool
     */
    public static function acceptCode(VoucherCodeInterface $voucherCode)
    {
        return true;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
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
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return float
     */
    public function getMinimumOrderValue()
    {
        return $this->minimumOrderValue;
    }

    /**
     * @param float $minimumOrderValue
     * @return $this
     */
    public function setMinimumOrderValue($minimumOrderValue)
    {
        $this->minimumOrderValue = $minimumOrderValue;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasMinimumOrderValue()
    {
        return !is_null($this->getMinimumOrderValue());
    }

    /**
     * @return \DateTime
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * @param \DateTime $validFrom
     * @return $this
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
     * @return $this
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
     * @return $this
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
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasCurrency()
    {
        return !is_null($this->getCurrency());
    }
}