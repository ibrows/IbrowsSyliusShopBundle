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
        )));

        $cartManager = $this->getCartManager();

        $this->assertCount(2, $voucherCartStrategy->compute($cart, $cartManager));
    }
}