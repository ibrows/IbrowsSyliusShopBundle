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
}