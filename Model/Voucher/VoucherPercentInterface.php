<?php

namespace Ibrows\SyliusShopBundle\Model\Voucher;

interface VoucherPercentInterface extends BaseVoucherInterface
{
    /**
     * @return \DateTime
     */
    public function getValidFrom();

    /**
     * @param \DateTime $validFrom
     * @return VoucherPercentInterface
     */
    public function setValidFrom(\DateTime $validFrom = null);

    /**
     * @return \DateTime
     */
    public function getValidTo();

    /**
     * @param \DateTime $validTo
     * @return VoucherPercentInterface
     */
    public function setValidTo(\DateTime $validTo = null);

    /**
     * @return int
     */
    public function getQuantity();

    /**
     * @return bool
     */
    public function hasQuantity();

    /**
     * @param int $quantity
     * @return VoucherPercentInterface
     */
    public function setQuantity($quantity);

    /**
     * @return float
     */
    public function getPercent();

    /**
     * @param float $percent
     * @return VoucherPercentInterface
     */
    public function setPercent($percent);
}