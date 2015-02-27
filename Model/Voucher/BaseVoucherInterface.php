<?php

namespace Ibrows\SyliusShopBundle\Model\Voucher;

interface BaseVoucherInterface
{
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
     * @param VoucherCodeInterface $voucherCode
     * @return bool
     */
    public static function acceptCode(VoucherCodeInterface $voucherCode);

    /**
     * @return string
     */
    public static function getPrefix();
}