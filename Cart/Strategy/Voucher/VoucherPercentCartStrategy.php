<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Voucher;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\BaseVoucherInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\VoucherCodeInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\VoucherPercentInterface;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;

class VoucherPercentCartStrategy extends VoucherCartStrategy
{
    /**
     * @param VoucherCodeInterface $voucherCode
     * @param CartInterface $cart
     * @return AdditionalCartItemInterface
     */
    protected function getAdditionalItemByVoucherCode(VoucherCodeInterface $voucherCode, CartInterface $cart)
    {
        $voucherCode->setValid(false);

        /** @var VoucherPercentInterface $voucher */
        if(!$voucher = $this->getValidVoucher($voucherCode)){
            return null;
        }

        $voucherCode->setValid(true);
        return $this->createAdditionalCartItem(
            round($cart->getItemsPriceTotalWithTax()*$voucher->getPercent()/5,2)*5*-1
        );
    }

    /**
     * @param VoucherCodeInterface $voucherCode
     * @param BaseVoucherInterface $voucher
     */
    protected function redeemVoucher(VoucherCodeInterface $voucherCode, BaseVoucherInterface $voucher)
    {
        $voucherCode->setRedeemedAt(new \DateTime());
    }
}