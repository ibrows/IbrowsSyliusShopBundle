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

    public function getPayedAt();

    /**
     * @param \DateTime $payedAt
     * @return mixed
     */
    public function setPayedAt(\DateTime $payedAt = null);
}