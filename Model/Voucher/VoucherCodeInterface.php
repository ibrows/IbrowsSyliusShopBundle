<?php

namespace Ibrows\SyliusShopBundle\Model\Voucher;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

interface VoucherCodeInterface
{
    /**
     * @return bool
     */
    public function isValid();

    /**
     * @param bool $valid
     * @return VoucherCodeInterface
     */
    public function setValid($valid = true);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param CartInterface $cart
     * @return VoucherCodeInterface
     */
    public function setCart(CartInterface $cart);

    /**
     * @return CartInterface
     */
    public function getCart();

    /**
     * @param string $code
     * @return VoucherCodeInterface
     */
    public function setCode($code);

    /**
     * @return bool
     */
    public function isRedeemed();

    /**
     * @param \DateTime $redeemedAt
     * @return VoucherCodeInterface
     */
    public function setRedeemedAt(\DateTime $redeemedAt);

    /**
     * @return \DateTime
     */
    public function getRedeemedAt();
}