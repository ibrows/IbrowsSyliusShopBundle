<?php

namespace Ibrows\SyliusShopBundle\Tests\Cart\Strategy\Voucher;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\Voucher\VoucherCartStrategy;
use Ibrows\SyliusShopBundle\Entity\AdditionalCartItem;
use Ibrows\SyliusShopBundle\Entity\Cart;
use Ibrows\SyliusShopBundle\Entity\Voucher;
use Ibrows\SyliusShopBundle\Entity\VoucherCode;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

abstract class AbstractVoucherCartStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $code
     * @param float $value
     * @param \DateTime $payedAt
     * @param string $currency
     * @return Voucher
     */
    protected function getVoucher($code, $value, \DateTime $payedAt = null, $currency = 'CHF')
    {
        $voucher = new Voucher();

        $voucher
            ->setCode($code)
            ->setPayedAt($payedAt)
            ->setCurrency($currency)
            ->setValue($value)
        ;

        return $voucher;
    }

    /**
     * @param string $code
     * @param \DateTime $redeemedAt
     * @return VoucherCode
     */
    protected function getVoucherCode($code, \DateTime $redeemedAt = null)
    {
        $voucherCode = new VoucherCode();

        $voucherCode
            ->setCode($code)
            ->setRedeemedAt($redeemedAt)
        ;

        return $voucherCode;
    }

    /**
     * @param Collection|Voucher[] $vouchers
     * @param \PHPUnit_Framework_MockObject_MockObject|EntityManager $em
     * @return VoucherCartStrategy
     */
    protected function getVoucherCartStrategy(Collection $vouchers = null, $em = null)
    {
        $vouchers = $vouchers ?: new ArrayCollection();

        $voucherClass = 'Ibrows\SyliusShopBundle\Entity\Voucher';

        /** @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject $voucherRepo */
        $voucherRepo = $this->getMock('Doctrine\ORM\EntityRepository', array(), array(), '', false);

        foreach($vouchers as $voucher){
            $voucherRepo->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnCallback(function(array $criterias)use($vouchers){
                    if(!isset($criterias['code'])){
                        return null;
                    }
                    foreach($vouchers as $voucher){
                        if($voucher->getCode() == $criterias['code']){
                            return $voucher;
                        }
                    }
                    return null;
                }))
            ;
        }

        /** @var RegistryInterface|\PHPUnit_Framework_MockObject_MockObject $registry */
        $registry = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');

        $em = $em ?: $this->getEntityManagerMock();

        $registry->expects($this->any())
            ->method('getRepository')
            ->with($voucherClass)
            ->will($this->returnValue($voucherRepo))
        ;

        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->with($voucherClass)
            ->will($this->returnValue($em))
        ;

        $voucherCartStrategy = new VoucherCartStrategy($registry, $voucherClass);
        $voucherCartStrategy->setEnabled(true);
        $voucherCartStrategy->setServiceId('cart.strategy.voucher');
        $voucherCartStrategy->setTaxincl(true);

        /** @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject $additionalCartItemRepo */
        $additionalCartItemRepo = $this->getMock('Doctrine\ORM\EntityRepository', array(), array(), '', false);

        $additionalCartItemRepo->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue('Ibrows\SyliusShopBundle\Entity\AdditionalCartItem'))
        ;

        $voucherCartStrategy->setAdditionalCartItemRepo($additionalCartItemRepo);

        return $voucherCartStrategy;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityManager
     */
    protected function getEntityManagerMock()
    {
        return $this->getMock('Doctrine\ORM\EntityManager', array(), array(), '', false);
    }

    /**
     * @return CartManager
     */
    protected function getCartManager()
    {
        /** @var CartManager|\PHPUnit_Framework_MockObject_MockObject $cartManager */
        $cartManager = $this->getMock('Ibrows\SyliusShopBundle\Cart\CartManager', array(), array(), '', false);

        return $cartManager;
    }

    /**
     * @param Collection|VoucherCode[] $voucherCodes
     * @param float $totalWithTax
     * @return Cart
     */
    protected function getCart(Collection $voucherCodes = null, $totalWithTax = null)
    {
        $voucherCodes = $voucherCodes ?: new ArrayCollection();

        /** @var Cart|\PHPUnit_Framework_MockObject_MockObject $cart */
        $cart = $this->getMock('Ibrows\SyliusShopBundle\Entity\Cart');

        $cart->expects($this->any())
            ->method('getVoucherCodes')
            ->will($this->returnValue($voucherCodes))
        ;

        $cart
            ->expects($this->any())
            ->method('getCurrency')
            ->will($this->returnValue('CHF'))
        ;

        $cart
            ->expects($this->any())
            ->method('getTotalWithTax')
            ->will($this->returnValue($totalWithTax))
        ;

        return $cart;
    }
}