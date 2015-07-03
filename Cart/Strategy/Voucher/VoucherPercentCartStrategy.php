<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Voucher;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\Voucher\Exception\VoucherRedemptionException;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\VoucherCodeInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\VoucherPercentInterface;

/**
 * Class VoucherPercentCartStrategy
 * @package Ibrows\SyliusShopBundle\Cart\Strategy\Voucher
 * @deprecated use VoucherPercentGroupedTaxCartStrategy
 */
class VoucherPercentCartStrategy extends AbstractVoucherCartStrategy
{
    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @throws VoucherRedemptionException
     */
    public function redeemVouchers(CartInterface $cart, CartManager $cartManager)
    {
        parent::redeemVouchers($cart, $cartManager);

        foreach ($cart->getAdditionalItemsByStrategy($this) as $additionalItem) {
            $data = $additionalItem->getStrategyData();

            foreach (array('newQuantity', 'voucherId', 'voucherClass') as $neededKey) {
                if (!array_key_exists($neededKey, $data)) {
                    throw new VoucherRedemptionException("Key $neededKey not found");
                }
            }

            /** @var VoucherPercentInterface $voucher */
            if (!$voucher = $this->voucherRepo->find($data['voucherId'])) {
                throw new VoucherRedemptionException("Voucher #" . $data['voucherId'] . " not found");
            }

            $voucher->setQuantity($data['newQuantity']);

            $this->voucherEntityManager->persist($voucher);
        }
    }

    /**
     * @param VoucherCodeInterface $voucherCode
     * @param CartInterface $cart
     * @param float $totalToReduce
     * @return AdditionalCartItemInterface
     */
    protected function getAdditionalItemByVoucherCode(VoucherCodeInterface $voucherCode, CartInterface $cart, &$totalToReduce)
    {
        if ($totalToReduce <= 0) {
            return null;
        }

        $voucher = $this->getVoucher($voucherCode);

        /** @var VoucherPercentInterface $voucher */
        if (!$voucher instanceof VoucherPercentInterface) {
            return null;
        }

        if (!$voucher->isValid() && !$voucherCode->isRedeemed()) {
            $voucherCode->setValid(false);
            return null;
        }

        $reduction = $cart->getItemsPriceTotalWithTax() * ($voucher->getPercent() / 100);
        $voucherCode->setValid(true);

        return $this->createAdditionalCartItemForVoucher($reduction, $voucherCode, $voucher);
    }

    /**
     * @param int $reduction
     * @param VoucherCodeInterface $voucherCode
     * @param VoucherPercentInterface $voucher
     * @param string $text
     * @return AdditionalCartItemInterface
     */
    protected function createAdditionalCartItemForVoucher($reduction, VoucherCodeInterface $voucherCode, VoucherPercentInterface $voucher, $text = null)
    {
        $quantity = $voucher->getQuantity();

        return $this->createAdditionalCartItem(
            $reduction * -1,
            $text,
            array(
                'percentRate'  => $voucher->getPercent(),
                'code'         => $voucherCode->getCode(),
                'reduction'    => $reduction,
                'validFrom'    => ($from = $voucher->getValidFrom()) ? $from->format('Y-m-d H:i:s') : null,
                'validTo'      => ($to = $voucher->getValidTo()) ? $to->format('Y-m-d H:i:s') : null,
                'quantity'     => $quantity,
                'newQuantity'  => $quantity - 1,
                'voucherId'    => $voucher->getId(),
                'voucherClass' => get_class($voucher)
            )
        );
    }
}