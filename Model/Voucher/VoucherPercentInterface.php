<?php

namespace Ibrows\SyliusShopBundle\Model\Voucher;

interface VoucherPercentInterface extends BaseVoucherInterface
{
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