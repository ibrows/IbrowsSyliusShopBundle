<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\VoucherCodeInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_voucher_code")
 * @ORM\InheritanceType("JOINED")
 */
class VoucherCode implements VoucherCodeInterface
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
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $valid;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", name="redeemed_at", nullable=true)
     */
    protected $redeemedAt;

    /**
     * @var CartInterface
     * @ORM\ManyToOne(targetEntity="Ibrows\SyliusShopBundle\Model\CartInterface", inversedBy="voucherCodes")
     */
    protected $cart;

    public function __construct()
    {
        $this->setValid(false);
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
     * @return VoucherCode
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Code : "' . $this->getCode() . '" (' . ($this->isValid() ? 'valid' : 'invalid') . ') ' . ($this->isRedeemed() ? ' / RedeemedAt: ' . $this->getRedeemedAt()->format('Y-m-d H:i:s') : null);
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * @return bool
     */
    public function isRedeemed()
    {
        return $this->getRedeemedAt() !== null;
    }

    /**
     * @param \DateTime $redeemedAt
     * @return VoucherCode
     */
    public function setRedeemedAt(\DateTime $redeemedAt = null)
    {
        $this->redeemedAt = $redeemedAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getRedeemedAt()
    {
        return $this->redeemedAt;
    }

    /**
     * @param bool $valid
     * @return VoucherCode
     */
    public function setValid($valid = true)
    {
        $this->valid = $valid;
        return $this;
    }

    /**
     * @param CartInterface $cart
     * @return VoucherCode
     */
    public function setCart(CartInterface $cart = null)
    {
        $this->cart = $cart;
        return $this;
    }

    /**
     * @return CartInterface
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}