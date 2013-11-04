<?php

namespace Ibrows\SyliusShopBundle\Model\Voucher;

interface BaseVoucherInterface
{
    /**
     * @return bool
     */
    public function isValid();

    /**
     * @param string $code
     * @return bool
     */
    public static function acceptCode($code);

    /**
     * @return string
     */
    public static function getPrefix();
}