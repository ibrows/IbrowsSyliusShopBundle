<?php

/**
 * Created by PhpStorm.
 * Project: claro.
 *
 * User: mikemeier
 * Date: 03.07.15
 * Time: 14:05
 */

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Voucher;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\AbstractCartStrategy;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartVoucherStrategyInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\VoucherCodeInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\VoucherPercentInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class VoucherPercentGroupedTaxCartStrategy extends AbstractCartStrategy implements CartVoucherStrategyInterface
{
    /**
     * @var EntityManager
     */
    protected $voucherEntityManager;

    /**
     * @var EntityRepository
     */
    protected $voucherRepo;

    /**
     * @var string
     */
    protected $voucherPercentClass;

    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var bool|float
     *                 Set to 0.05 for rounding to 5 cents (Switzerland for example)
     */
    protected $roundToNearest = false;

    /**
     * @param RegistryInterface $doctrine
     * @param string            $voucherPercentClass
     */
    public function __construct(RegistryInterface $doctrine, $voucherPercentClass)
    {
        $this->doctrine = $doctrine;
        $this->voucherEntityManager = $doctrine->getManagerForClass($voucherPercentClass);
        $this->voucherRepo = $doctrine->getRepository($voucherPercentClass);
        $this->voucherPercentClass = $voucherPercentClass;
        $this->setTaxincl(false);
    }

    /**
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     *
     * @return bool
     */
    public function accept(CartInterface $cart, CartManager $cartManager)
    {
        return $cart->getItemsPriceTotal() > 0 && $cart->countItems() > 0;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     *
     * @return AdditionalCartItemInterface[]
     */
    public function compute(CartInterface $cart, CartManager $cartManager)
    {
        $items = array();

        foreach ($cart->getVoucherCodes() as $voucherCode) {
            /** @var VoucherPercentInterface $voucherClass */
            $voucherClass = $this->voucherPercentClass;
            if (!$voucherClass::acceptCode($voucherCode)) {
                continue;
            }

            if (!$voucher = $this->getVoucher($voucherCode)) {
                continue;
            }

            $additionalItems = $this->getAdditionalItemsForVoucher($cart, $voucherCode, $voucher);

            if (is_array($additionalItems)) {
                foreach ($additionalItems as $item) {
                    $items[] = $item;
                }

                break;
            }
        }

        return $items;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     */
    public function redeemVouchers(CartInterface $cart, CartManager $cartManager)
    {
        foreach ($cart->getVoucherCodes() as $voucherCode) {
            if (!$voucherCode->isValid() || $voucherCode->isRedeemed()) {
                continue;
            }

            if (!$voucher = $this->getVoucher($voucherCode)) {
                continue;
            }

            if (!$voucher->isValid()) {
                continue;
            }

            if ($voucher->hasCurrency() && $cart->getCurrency() != $voucher->getCurrency()) {
                $voucherCode->setValid(false);
                continue;
            }

            $voucherCode->setRedeemedAt(new \DateTime());
            $voucher->setQuantity($voucher->getQuantity() - 1);
        }
    }

    /**
     * @return bool True if multiple vouchers are cumulative, false otherwise
     */
    public function isCumulative()
    {
        return false;
    }

    /**
     * @return bool|float
     */
    public function getRoundToNearest()
    {
        return $this->roundToNearest;
    }

    /**
     * @param bool|float $roundToNearest
     */
    public function setRoundToNearest($roundToNearest)
    {
        $this->roundToNearest = $roundToNearest;
    }

    /**
     * @param CartInterface           $cart
     * @param VoucherCodeInterface    $voucherCode
     * @param VoucherPercentInterface $voucher
     *
     * @return AdditionalCartItemInterface[]|null
     */
    protected function getAdditionalItemsForVoucher(CartInterface $cart, VoucherCodeInterface $voucherCode, VoucherPercentInterface $voucher)
    {
        if (!$voucher->isValid() && !$voucherCode->isRedeemed()) {
            $voucherCode->setValid(false);

            return;
        }

        $voucherCode->setValid(true);
        $taxGroups = array();

        $total = 0;
        foreach ($cart->getItems() as $item) {
            $taxRate = sprintf('%.2f', round($item->getTaxRate(), 2));
            if (!isset($taxGroups[$taxRate])) {
                $taxGroups[$taxRate] = 0;
            }

            $itemPrice = $this->getTaxincl() ? $item->getTotalWithTaxPrice() : $item->getTotal();
            $taxGroups[$taxRate] += $itemPrice;
            $total += $itemPrice;
        }

        if ($voucher->hasMinimumOrderValue() && $total < $voucher->getMinimumOrderValue()) {
            $voucherCode->setValid(false);

            return;
        }

        $quantity = $voucher->getQuantity();

        $items = array();
        foreach ($taxGroups as $taxRate => $value) {
            $reduction = $value / 100 * $voucher->getPercent() * -1;
            if (false !== ($roundToNearest = $this->getRoundToNearest())) {
                $reduction = $this->roundToNearest($reduction, $roundToNearest);
            }

            $item = $this->createAdditionalCartItem(
                $reduction,
                null,
                array(
                    'percentRate' => $voucher->getPercent(),
                    'code' => $voucherCode->getCode(),
                    'reduction' => $reduction,
                    'validFrom' => ($from = $voucher->getValidFrom()) ? $from->format('Y-m-d H:i:s') : null,
                    'validTo' => ($to = $voucher->getValidTo()) ? $to->format('Y-m-d H:i:s') : null,
                    'quantity' => $quantity,
                    'voucherId' => $voucher->getId(),
                    'voucherClass' => get_class($voucher),
                    'taxRate' => $taxRate,
                    'value' => $value,
                )
            );

            $item->setTaxInclusive($this->getTaxincl());
            $item->setTaxRate($taxRate);

            $items[] = $item;
        }

        return $items;
    }

    /**
     * @param VoucherCodeInterface $voucherCode
     *
     * @return VoucherPercentInterface
     */
    protected function getVoucher(VoucherCodeInterface $voucherCode)
    {
        return $this->voucherRepo->findOneBy(
            array(
                'code' => $voucherCode->getCode(),
            )
        );
    }
}
