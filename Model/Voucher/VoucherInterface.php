<?php

namespace Ibrows\SyliusShopBundle\Model\Voucher;

interface VoucherInterface extends BaseVoucherInterface
{
    /**
     * @return float
     */
    public function getValue();

    /**
     * @param float $value
     * @return VoucherInterface
     */
    public function setValue($value);

    /**
     * @param string $currency
     * @return VoucherInterface
     */
    public function setCurrency($currency);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getCurrency();
}