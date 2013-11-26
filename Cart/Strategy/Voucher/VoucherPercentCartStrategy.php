<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Voucher;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\BaseVoucherInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\VoucherCodeInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\VoucherPercentInterface;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class VoucherPercentCartStrategy extends VoucherCartStrategy
{
    /**
     * @param RegistryInterface $doctrine
     * @param string $voucherClass
     * @param bool $cumulative
     */
    public function __construct(RegistryInterface $doctrine, $voucherClass, $cumulative = false)
    {
        parent::__construct($doctrine, $voucherClass, $cumulative);
    }

    /**
     * @param VoucherCodeInterface $voucherCode
     * @param CartInterface $cart
     * @param float $totalToReduce
     * @return AdditionalCartItemInterface
     */
    protected function getAdditionalItemByVoucherCode(VoucherCodeInterface $voucherCode, CartInterface $cart, &$totalToReduce)
    {
        /** @var VoucherPercentInterface $voucher */
        if(!$voucher = $this->getVoucher($voucherCode)){
            $voucherCode->setValid(false);
            return null;
        }

        if(!$voucher->isValid() && !$voucherCode->isRedeemed()){
            $voucherCode->setValid(false);
            return null;
        }

        $voucherCode->setValid(true);

        return $this->createAdditionalCartItem(
            $cart->getItemsPriceTotalWithTax()*$voucher->getPercent()*-1,
            null,
            array(
                'percentRate' => $voucher->getPercent()*100,
                'code' => $voucherCode->getCode(),
                'validFrom' => ($from = $voucher->getValidFrom()) ? $from->format('Y-m-d H:i:s') : null,
                'validTo' => ($to = $voucher->getValidTo()) ? $to->format('Y-m-d H:i:s') : null,
                'quantity' => $voucher->getQuantity()
            )
        );
    }

    /**
     * @param VoucherCodeInterface $voucherCode
     * @param BaseVoucherInterface $voucher
     * @param float $totalToReduce
     */
    protected function redeemVoucher(VoucherCodeInterface $voucherCode, BaseVoucherInterface $voucher, &$totalToReduce)
    {
        $voucherCode->setRedeemedAt(new \DateTime());
        if(!$voucher instanceof VoucherPercentInterface){
            return;
        }

        if($voucher->hasQuantity()){
            $voucher->setQuantity($voucher->getQuantity()-1);
        }
    }
}