<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Voucher;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\Strategy\AbstractCartStrategy;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartVoucherStrategyInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\VoucherCodeInterface;
use Ibrows\SyliusShopBundle\Model\Voucher\VoucherInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

abstract class AbstractVoucherCartStrategy extends AbstractCartStrategy implements CartVoucherStrategyInterface
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
     * @param string            $voucherClass
     * @param bool              $cumulative
     */
    public function __construct(RegistryInterface $doctrine, $voucherClass, $cumulative = true)
    {
        $this->doctrine = $doctrine;
        $this->voucherEntityManager = $doctrine->getManagerForClass($voucherClass);
        $this->voucherRepo = $doctrine->getRepository($voucherClass);
        $this->voucherClass = $voucherClass;
        $this->cumulative = $cumulative;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     */
    public function redeemVouchers(CartInterface $cart, CartManager $cartManager)
    {
        foreach ($cart->getVoucherCodes() as $voucherCode) {
            if ($voucherCode->isValid() && !$voucherCode->isRedeemed()) {
                $voucherCode->setRedeemedAt(new \DateTime());
            }
        }
    }

    /**
     * @return bool
     */
    public function isCumulative()
    {
        return $this->cumulative;
    }

    /**
     * @param bool $cumulative
     *
     * @return VoucherCartStrategy
     */
    public function setCumulative($cumulative)
    {
        $this->cumulative = $cumulative;

        return $this;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     *
     * @return AdditionalCartItemInterface[]
     */
    public function compute(CartInterface $cart, CartManager $cartManager)
    {
        $additionalItems = array();
        $totalToReduce = $cart->getTotalWithTax();

        foreach ($cart->getVoucherCodes() as $voucherCode) {
            /** @var VoucherInterface $voucherClass */
            $voucherClass = $this->voucherClass;

            if (!$voucherClass::acceptCode($voucherCode)) {
                continue;
            }

            $additionalItem = $this->getAdditionalItemByVoucherCode($voucherCode, $cart, $totalToReduce);

            if ($additionalItem) {
                $additionalItems[] = $additionalItem;
                if (!$this->isCumulative()) {
                    break;
                }
            }
        }

        return $additionalItems;
    }

    /**
     * @param CartInterface $cart
     * @param CartManager   $cartManager
     *
     * @return bool
     */
    public function accept(CartInterface $cart, CartManager $cartManager)
    {
        return true;
    }

    /**
     * @param VoucherCodeInterface $voucherCode
     * @param CartInterface        $cart
     * @param float                $totalToReduce
     *
     * @return AdditionalCartItemInterface[]
     */
    abstract protected function getAdditionalItemByVoucherCode(VoucherCodeInterface $voucherCode, CartInterface $cart, &$totalToReduce);

    /**
     * @param VoucherCodeInterface $voucherCode
     *
     * @return VoucherInterface
     */
    protected function getVoucher(VoucherCodeInterface $voucherCode)
    {
        /* @var VoucherInterface $voucher */
        return $this->voucherRepo->findOneBy(
            array(
                'code' => $voucherCode->getCode(),
            )
        );
    }
}
