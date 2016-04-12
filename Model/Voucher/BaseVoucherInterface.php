<?php

namespace Ibrows\SyliusShopBundle\Model\Voucher;

interface BaseVoucherInterface
{
    /**
     * @param VoucherCodeInterface $voucherCode
     *
     * @return bool
     */
    public static function acceptCode(VoucherCodeInterface $voucherCode);

    /**
     * @return bool
     */
    public function isValid();

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return int
     */
    public function getQuantity();

    /**
     * @param int $quantity
     *
     * @return BaseVoucherInterface
     */
    public function setQuantity($quantity);

    /**
     * @return bool
     */
    public function hasQuantity();

    /**
     * @return \DateTime
     */
    public function getValidFrom();

    /**
     * @param \DateTime $validFrom
     *
     * @return BaseVoucherInterface
     */
    public function setValidFrom(\DateTime $validFrom = null);

    /**
     * @return \DateTime
     */
    public function getValidTo();

    /**
     * @param \DateTime $validTo
     *
     * @return BaseVoucherInterface
     */
    public function setValidTo(\DateTime $validTo = null);

    /**
     * @return float
     */
    public function getMinimumOrderValue();

    /**
     * @param float $minimumOrderValue
     *
     * @return BaseVoucherInterface
     */
    public function setMinimumOrderValue($minimumOrderValue);

    /**
     * @return bool
     */
    public function hasMinimumOrderValue();

    /**
     * @return string
     */
    public function getCurrency();

    /**
     * @param string $currency
     *
     * @return BaseVoucherInterface
     */
    public function setCurrency($currency);

    /**
     * @return bool
     */
    public function hasCurrency();
}
