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
     * @var bool
     */
    protected $cumulative = true;

    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @param RegistryInterface $doctrine
     * @param string $voucherClass
     * @param bool $cumulative
     */
    public function __construct(RegistryInterface $doctrine, $voucherClass, $cumulative = true)
    {
        $this->doctrine = $doctrine;
        $this->voucherRepo = $doctrine->getRepository($voucherClass);
        $this->voucherClass = $voucherClass;
        $this->cumulative = $cumulative;
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

        $totalToReduce = $cart->getTotalWithTax();

        foreach($cart->getVoucherCodes() as $voucherCode){
            /** @var VoucherInterface $voucherClass */
            $voucherClass = $this->voucherClass;

            if(!$voucherClass::acceptCode($voucherCode)){
                continue;
            }

            if($additionalItem = $this->getAdditionalItemByVoucherCode($voucherCode, $cart, $totalToReduce)){
                $additionalItems[] = $additionalItem;
                if(!$this->isCumulative()){
                    break;
                }
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
     * @param float $totalToReduce
     * @return AdditionalCartItemInterface
     */
    protected function getAdditionalItemByVoucherCode(VoucherCodeInterface $voucherCode, CartInterface $cart, &$totalToReduce)
    {
        if($totalToReduce <= 0){
            $voucherCode->setValid(false);
            return null;
        }

        /** @var VoucherInterface $voucher */
        if(!$voucher = $this->getVoucher($voucherCode)){
            $voucherCode->setValid(false);
            return null;
        }

        if(!$voucher->isValid() && !$voucherCode->isRedeemed()){
            $voucherCode->setValid(false);
            return null;
        }

        if($cart->getCurrency() != $voucher->getCurrency()){
            $voucherCode->setValid(false);
            return null;
        }

        $voucherCode->setValid(true);

        $voucherValue = $voucher->getValue();
        if($voucherValue <= $totalToReduce){
            $voucherReduction = $voucher->getValue()*-1;
            $totalToReduce = $totalToReduce - $voucherValue;
        }else{
            $voucherReduction = $totalToReduce*-1;
            $totalToReduce = 0;
        }

        return $this->createAdditionalCartItem($voucherReduction, null, array(
            'code' => $voucherCode->getCode(),
            'reduction' => $voucherReduction
        ));
    }

    /**
     * @param CartInterface $cart
     * @param CartManager $cartManager
     */
    public function redeemVouchers(CartInterface $cart, CartManager $cartManager)
    {
        $totalToReduce = $cart->getTotalWithTax();

        foreach($cart->getVoucherCodes() as $voucherCode){
            /** @var VoucherInterface $voucherClass */
            $voucherClass = $this->voucherClass;

            if(
                !$voucherClass::acceptCode($voucherCode) OR
                !$voucherCode->isValid() OR
                $voucherCode->isRedeemed() OR
                !($voucher = $this->getValidVoucher($voucherCode))
            ){
                continue;
            }

            $this->redeemVoucher($voucherCode, $voucher, $totalToReduce);
        }
    }

    /**
     * @param VoucherCodeInterface $voucherCode
     * @param BaseVoucherInterface $voucher
     * @param float $totalToReduce
     */
    protected function redeemVoucher(VoucherCodeInterface $voucherCode, BaseVoucherInterface $voucher, &$totalToReduce)
    {
        if($totalToReduce <= 0){
            return;
        }

        $voucherCode->setRedeemedAt(new \DateTime());

        if(!$voucher instanceof VoucherInterface){
            return;
        }

        $voucherValue = $voucher->getValue();

        if($voucherValue <= $totalToReduce){
            $newVoucherValue = 0;
            $totalToReduce = $totalToReduce - $voucherValue;
        }else{
            $newVoucherValue = $voucherValue - $totalToReduce;
            $totalToReduce = 0;
        }

        $voucher->setValue($newVoucherValue);

        $em = $this->doctrine->getManagerForClass(get_class($voucher));
        $em->persist($voucher);
    }

    /**
     * @return boolean
     */
    public function isCumulative()
    {
        return $this->cumulative;
    }

    /**
     * @param boolean $cumulative
     * @return VoucherCartStrategy
     */
    public function setCumulative($cumulative)
    {
        $this->cumulative = $cumulative;
        return $this;
    }
}