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

class VoucherCartStrategyTest extends AbstractVoucherCartStrategyTest
{
    public function testAccept()
    {
        $cart = $this->getCart();
        $cartManager = $this->getCartManager();

        $this->assertTrue($this->getVoucherCartStrategy()->accept($cart, $cartManager));
    }

    public function testPrefix()
    {
        $this->assertSame('v', Voucher::getPrefix());
    }

    public function testEmptyCompute()
    {
        $voucherCartStrategy = $this->getVoucherCartStrategy();

        $cart = $this->getCart();
        $cartManager = $this->getCartManager();

        $this->assertCount(0, $voucherCartStrategy->compute($cart, $cartManager));
    }

    public function testNotPayedAndPayedCompute()
    {
        $voucherCartStrategy = $this->getVoucherCartStrategy(new ArrayCollection(array(
            $this->getVoucher('ab', 50, new \DateTime()),
            $this->getVoucher('cd', 100, new \DateTime()),
            $this->getVoucher('ef', 150)
        )));

        $prefix = Voucher::getPrefix();
        $cart = $this->getCart(new ArrayCollection(array(
            $this->getVoucherCode($prefix.'ab'),
            $this->getVoucherCode($prefix.'cd'),
            $this->getVoucherCode($prefix.'ef')
        )), 5000);

        $cartManager = $this->getCartManager();
        $additionalItems = $voucherCartStrategy->compute($cart, $cartManager);

        $this->assertCount(2, $additionalItems);

        $search = array(-50, -100);
        foreach($additionalItems as $item){
            $this->assertNotSame(false, $key = array_search($item->getPriceWithTax(), $search));
            if(false !== $key){
                unset($search[$key]);
            }
        }
    }

    public function testWrongCurrency()
    {
        $voucherCartStrategy = $this->getVoucherCartStrategy(new ArrayCollection(array(
            $this->getVoucher('ab', 50, new \DateTime()),
            $this->getVoucher('cd', 100, new \DateTime(), 'EUR'),
            $this->getVoucher('ef', 150)
        )));

        $prefix = Voucher::getPrefix();
        $cart = $this->getCart(new ArrayCollection(array(
            $this->getVoucherCode($prefix.'ab'),
            $this->getVoucherCode($prefix.'cd'),
            $this->getVoucherCode($prefix.'ef')
        )), 5000);

        $cartManager = $this->getCartManager();
        $additionalItems = $voucherCartStrategy->compute($cart, $cartManager);

        $this->assertCount(1, $additionalItems);

        $search = array(-50);
        foreach($additionalItems as $item){
            $this->assertNotSame(false, $key = array_search($item->getPriceWithTax(), $search));
            if(false !== $key){
                unset($search[$key]);
            }
        }
    }

    public function testCartAmountLowerThanVouchersCompute()
    {
        $voucherCartStrategy = $this->getVoucherCartStrategy(new ArrayCollection(array(
            $this->getVoucher('ab', 50, new \DateTime()),
            $this->getVoucher('cd', 100, new \DateTime()),
            $this->getVoucher('ef', 150)
        )));

        $prefix = Voucher::getPrefix();
        $cart = $this->getCart(new ArrayCollection(array(
            $this->getVoucherCode($prefix.'ab'),
            $this->getVoucherCode($prefix.'cd'),
            $this->getVoucherCode($prefix.'ef')
        )), 80);

        $cartManager = $this->getCartManager();
        $additionalItems = $voucherCartStrategy->compute($cart, $cartManager);

        $this->assertCount(2, $additionalItems);

        $search = array(-50, -30);
        foreach($additionalItems as $item){
            $this->assertNotSame(false, $key = array_search($item->getPriceWithTax(), $search));
            if(false !== $key){
                unset($search[$key]);
            }
        }
    }

    public function testNotCumulative()
    {
        $voucherCartStrategy = $this->getVoucherCartStrategy(new ArrayCollection(array(
            $this->getVoucher('ab', 50, new \DateTime()),
            $this->getVoucher('cd', 100, new \DateTime()),
            $this->getVoucher('ef', 150)
        )));

        $voucherCartStrategy->setCumulative(false);

        $prefix = Voucher::getPrefix();
        $cart = $this->getCart(new ArrayCollection(array(
            $this->getVoucherCode($prefix.'ab'),
            $this->getVoucherCode($prefix.'cd'),
            $this->getVoucherCode($prefix.'ef')
        )), 5000);

        $cartManager = $this->getCartManager();
        $additionalItems = $voucherCartStrategy->compute($cart, $cartManager);

        $this->assertCount(1, $additionalItems);

        $search = array(-50);
        foreach($additionalItems as $item){
            $this->assertNotSame(false, $key = array_search($item->getPriceWithTax(), $search));
            if(false !== $key){
                unset($search[$key]);
            }
        }
    }

    public function testValidVouchers()
    {
        /** @var Voucher[] $vouchers */
        $vouchers = array(
            $this->getVoucher('ab', 50, new \DateTime()),
            $this->getVoucher('cd', 100, new \DateTime()),
            $this->getVoucher('ef', 150)
        );

        $prefix = Voucher::getPrefix();
        /** @var VoucherCode[] $voucherCodes */
        $voucherCodes = array(
            $this->getVoucherCode($prefix.'ab'),
            $this->getVoucherCode($prefix.'cd'),
            $this->getVoucherCode($prefix.'ef')
        );

        $cartTotal = 5500.50;

        $em = $this->getEntityManagerMock();
        $em->expects($this->exactly(2))
            ->method('persist')
        ;

        $voucherCartStrategy = $this->getVoucherCartStrategy(new ArrayCollection($vouchers), $em);
        $cart = $this->getCart(new ArrayCollection($voucherCodes), $cartTotal);

        $this->assertSame($cartTotal, $cart->getTotalWithTax());

        $cartManager = $this->getCartManager();
        $additionalItems = $voucherCartStrategy->compute($cart, $cartManager);

        $cart->expects($this->any())
            ->method('getAdditionalItemsByStrategy')
            ->with($voucherCartStrategy)
            ->will($this->returnValue($additionalItems))
        ;

        foreach($voucherCodes as $voucherCode){
            $this->assertFalse($voucherCode->isRedeemed());
        }

        // 1 and 2 valid, 3 not payed
        $this->assertTrue($vouchers[0]->isValid());
        $this->assertTrue($vouchers[1]->isValid());
        $this->assertFalse($vouchers[2]->isValid());

        $voucherCartStrategy->redeemVouchers($cart, $cartManager);

        // 1 and 2 redeemed, 3 not valid -> not redeemed
        $this->assertTrue($voucherCodes[0]->isRedeemed());
        $this->assertTrue($voucherCodes[1]->isRedeemed());
        $this->assertFalse($voucherCodes[2]->isRedeemed());

        // Check amount of vouchers
        $this->assertSame(0, $vouchers[0]->getValue());
        $this->assertSame(0, $vouchers[1]->getValue());
        $this->assertFalse($voucherCodes[2]->isValid());
    }

    public function testValidVouchersWithCartAmountLowerThanVouchers()
    {
        /** @var Voucher[] $vouchers */
        $vouchers = array(
            $this->getVoucher('ab', 50, new \DateTime()),
            $this->getVoucher('cd', 100, new \DateTime()),
            $this->getVoucher('ef', 800, new \DateTime()),
            $this->getVoucher('gh', 150)
        );

        $prefix = Voucher::getPrefix();
        /** @var VoucherCode[] $voucherCodes */
        $voucherCodes = array(
            $this->getVoucherCode($prefix.'ab'),
            $this->getVoucherCode($prefix.'cd'),
            $this->getVoucherCode($prefix.'ef'),
            $this->getVoucherCode($prefix.'gh'),
        );

        $cartTotal = 80;

        $em = $this->getEntityManagerMock();
        $em->expects($this->exactly(2))
            ->method('persist')
        ;

        $voucherCartStrategy = $this->getVoucherCartStrategy(new ArrayCollection($vouchers), $em);
        $cart = $this->getCart(new ArrayCollection($voucherCodes), $cartTotal);

        $this->assertSame($cartTotal, $cart->getTotalWithTax());

        $cartManager = $this->getCartManager();

        $additionalItems = $voucherCartStrategy->compute($cart, $cartManager);
        $cart->expects($this->any())
            ->method('getAdditionalItemsByStrategy')
            ->with($voucherCartStrategy)
            ->will($this->returnValue($additionalItems))
        ;

        foreach($voucherCodes as $voucherCode){
            $this->assertFalse($voucherCode->isRedeemed());
        }

        // 1, 2 and 3 valid, 4 not payed
        $this->assertTrue($vouchers[0]->isValid());
        $this->assertTrue($vouchers[1]->isValid());
        $this->assertTrue($vouchers[2]->isValid());
        $this->assertFalse($vouchers[3]->isValid());

        $voucherCartStrategy->redeemVouchers($cart, $cartManager);

        // 1 and 2 redeemed, 3 not used because of total amount of cart, 4 not valid -> not redeemed
        $this->assertTrue($voucherCodes[0]->isRedeemed());
        $this->assertTrue($voucherCodes[1]->isRedeemed());
        $this->assertFalse($voucherCodes[2]->isRedeemed());
        $this->assertFalse($voucherCodes[3]->isRedeemed());

        // Check amount of vouchers
        $this->assertSame(0, $vouchers[0]->getValue());
        $this->assertSame(70, $vouchers[1]->getValue());
        $this->assertSame(800, $vouchers[2]->getValue());
        $this->assertFalse($voucherCodes[3]->isValid());
    }
}