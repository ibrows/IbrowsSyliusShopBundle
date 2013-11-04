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
     * @return VoucherCodeInterface
     */
    public function setRedeemedAt(\DateTime $redeemedAt)
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
     * @return VoucherCodeInterface
     */
    public function setValid($valid = true)
    {
        $this->valid = $valid;
        return $this;
    }

    /**
     * @param CartInterface $cart
     * @return VoucherCodeInterface
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
}