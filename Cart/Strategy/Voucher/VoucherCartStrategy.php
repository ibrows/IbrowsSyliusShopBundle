<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Voucher;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\Voucher\Exception\VoucherRedemptionException;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\VoucherCodeInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\VoucherInterface;

class VoucherCartStrategy extends AbstractVoucherCartStrategy
{
    /**
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     *
     * @throws VoucherRedemptionException
     */
    public function redeemVouchers(CartInterface $cart, CartManager $cartManager)
    {
        parent::redeemVouchers($cart, $cartManager);

        foreach ($cart->getAdditionalItemsByStrategy($this) as $additionalItem) {
            $data = $additionalItem->getStrategyData();

            foreach (array('newValue', 'voucherId') as $neededKey) {
                if (!array_key_exists($neededKey, $data)) {
                    throw new VoucherRedemptionException("Key $neededKey not found");
                }
            }

            /** @var VoucherInterface $voucher */
            if (!$voucher = $this->voucherRepo->find($data['voucherId'])) {
                throw new VoucherRedemptionException('Voucher #'.$data['voucherId'].' not found');
            }

            if ($voucher->hasQuantity()) {
                $voucher->setQuantity($voucher->getQuantity() - 1);
            } else {
                $voucher->setValue($data['newValue']);
            }

            $this->voucherEntityManager->persist($voucher);
        }
    }

    /**
     * @param VoucherCodeInterface $voucherCode
     * @param CartInterface        $cart
     * @param float                $totalToReduce
     *
     * @return AdditionalCartItemInterface
     */
    protected function getAdditionalItemByVoucherCode(VoucherCodeInterface $voucherCode, CartInterface $cart, &$totalToReduce)
    {
        if ($totalToReduce <= 0) {
            return;
        }

        $voucher = $this->getVoucher($voucherCode);

        if (!$voucher instanceof VoucherInterface) {
            return;
        }

        if (!$voucher->isValid() && !$voucherCode->isRedeemed()) {
            $voucherCode->setValid(false);

            return;
        }

        if ($voucher->hasCurrency() && $cart->getCurrency() != $voucher->getCurrency()) {
            $voucherCode->setValid(false);

            return;
        }

        if ($voucher->hasQuantity() && $voucher->hasMinimumOrderValue()) {
            if ($cart->getTotal() < $voucher->getMinimumOrderValue()) {
                $voucherCode->setValid(false);

                return;
            }
        }

        $voucherCode->setValid(true);
        $voucherValue = $voucher->getValue();

        if ($voucherValue <= $totalToReduce) {
            $reduction = $voucherValue;
            $totalToReduce = $totalToReduce - $voucherValue;
        } else {
            $reduction = $totalToReduce;
            $totalToReduce = 0;
        }

        return $this->createAdditionalCartItemForVoucher($reduction, $voucherCode, $voucher);
    }

    /**
     * @param VoucherCodeInterface $voucherCode
     *
     * @return VoucherInterface
     */
    protected function getValidVoucher(VoucherCodeInterface $voucherCode)
    {
        $voucher = $this->getVoucher($voucherCode);

        if (!$voucher instanceof VoucherInterface or !$voucher->isValid()) {
            return;
        }

        return $voucher;
    }

    /**
     * @param int                  $reduction
     * @param VoucherCodeInterface $voucherCode
     * @param VoucherInterface     $voucher
     * @param string               $text
     *
     * @return AdditionalCartItemInterface
     */
    protected function createAdditionalCartItemForVoucher($reduction, VoucherCodeInterface $voucherCode, VoucherInterface $voucher, $text = null)
    {
        return $this->createAdditionalCartItem(
            $reduction * -1,
            $text,
            array(
                'code' => $voucherCode->getCode(),
                'reduction' => $reduction,
                'newValue' => $voucher->getValue() - $reduction,
                'voucherId' => $voucher->getId(),
                'voucherClass' => get_class($voucher),
            )
        );
    }
}
