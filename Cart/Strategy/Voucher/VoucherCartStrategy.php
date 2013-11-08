<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Voucher;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\AbstractCartStrategy;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartVoucherStrategyInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\VoucherCodeInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\VoucherInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\BaseVoucherInterface;
use Doctrine\ORM\EntityRepository;

class VoucherCartStrategy extends AbstractCartStrategy implements CartVoucherStrategyInterface
{
    /**
     * @var EntityRepository
     */
    protected $voucherRepo;

    /**
     * @var string
     */
    protected $voucherClass;

    /**
     * @param RegistryInterface $doctrine
     * @param string $voucherClass
     */
    public function __construct(RegistryInterface $doctrine, $voucherClass)
    {
        $this->voucherRepo = $doctrine->getRepository($voucherClass);
        $this->voucherClass = $voucherClass;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return bool
     */
    public function accept(CartInterface $cart, CartManager $cartManager)
    {
        return true;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     * @return AdditionalCartItemInterface[]
     */
    public function compute(CartInterface $cart, CartManager $cartManager)
    {
        $additionalItems = array();
        foreach($cart->getVoucherCodes() as $voucherCode){
            /** @var VoucherInterface $voucherClass */
            $voucherClass = $this->voucherClass;

            if(!$voucherClass::acceptCode($voucherCode->getCode())){
                continue;
            }

            if($additionalItem = $this->getAdditionalItemByVoucherCode($voucherCode, $cart)){
                $additionalItems[] = $additionalItem;
            }
        }
        return $additionalItems;
    }

    /**
     * @param VoucherCodeInterface $voucherCode
     * @return VoucherInterface
     */
    protected function getVoucher(VoucherCodeInterface $voucherCode)
    {
        /** @var BaseVoucherInterface $voucherClass */
        $voucherClass = $this->voucherClass;

        /** @var VoucherInterface $voucher */
        return $this->voucherRepo->findOneBy(array(
            'code' => substr($voucherCode->getCode(), strlen($voucherClass::getPrefix()))
        ));
    }

    /**
     * @param VoucherCodeInterface $voucherCode
     * @return VoucherInterface
     */
    protected function getValidVoucher(VoucherCodeInterface $voucherCode)
    {
        $voucher = $this->getVoucher($voucherCode);

        if(!$voucher OR !$voucher->isValid()){
            return null;
        }

        return $voucher;
    }

    /**
     * @param VoucherCodeInterface $voucherCode
     * @param CartInterface $cart
     * @return AdditionalCartItemInterface
     */
    protected function getAdditionalItemByVoucherCode(VoucherCodeInterface $voucherCode, CartInterface $cart)
    {
        /** @var VoucherInterface $voucher */
        if(!$voucher = $this->getVoucher($voucherCode)){
            $voucherCode->setValid(false);
            return null;
        }

        if(!$voucher->isValid() && !$voucherCode->isRedeemed()){
            $voucherCode->setValid(false);
            return null;
        }

        if(!$cart->getCurrency() == $voucher->getCurrency()){
            $voucherCode->setValid(false);
            return null;
        }

        $voucherCode->setValid(true);

        return $this->createAdditionalCartItem($voucher->getValue()*-1);
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     */
    public function redeemVouchers(CartInterface $cart, CartManager $cartManager)
    {
        foreach($cart->getVoucherCodes() as $voucherCode){
            /** @var VoucherInterface $voucherClass */
            $voucherClass = $this->voucherClass;

            if(
                !$voucherClass::acceptCode($voucherCode->getCode()) OR
                !$voucherCode->isValid() OR
                $voucherCode->isRedeemed() OR
                !($voucher = $this->getValidVoucher($voucherCode))
            ){
                continue;
            }

            $this->redeemVoucher($voucherCode, $voucher);
        }
    }

    /**
     * @param VoucherCodeInterface $voucherCode
     * @param BaseVoucherInterface $voucher
     * @todo reduce voucher value (strategy for sorting vouchers and create a new one)
     */
    protected function redeemVoucher(VoucherCodeInterface $voucherCode, BaseVoucherInterface $voucher)
    {
        $voucherCode->setRedeemedAt(new \DateTime());
        if(!$voucher instanceof VoucherInterface){
            return;
        }

        // here reduction
    }
}