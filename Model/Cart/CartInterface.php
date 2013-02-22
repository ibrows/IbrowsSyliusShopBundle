<?php

namespace Ibrows\SyliusShopBundle\Model\Cart;

use Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface;
use Ibrows\SyliusShopBundle\Model\Delivery\DeliveryOptionsInterface;
use Ibrows\SyliusShopBundle\Model\Payment\PaymentOptionsInterface;

use Sylius\Bundle\CartBundle\Model\CartInterface as BaseCartInterface;

use DateTime;

interface CartInterface extends BaseCartInterface
{
    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param string $email
     * @return CartInterface
     */
    public function setEmail($email);

    /**
     * @return InvoiceAddressInterface
     */
    public function getInvoiceAddress();

    /**
     * @param InvoiceAddressInterface $invoiceAddress
     * @return CartInterface
     */
    public function setInvoiceAddress(InvoiceAddressInterface $invoiceAddress = null);

    /**
     * @return DeliveryAddressInterface
     */
    public function getDeliveryAddress();

    /**
     * @param DeliveryAddressInterface $deliveryAddress
     * @return CartInterface
     */
    public function setDeliveryAddress(DeliveryAddressInterface $deliveryAddress = null);

    /**
     * @return DeliveryOptionsInterface
     */
    public function getDeliveryOptions();

    /**
     * @param DeliveryOptionsInterface $deliveryOptions
     * @return CartInterface
     */
    public function setDeliveryOptions(DeliveryOptionsInterface $deliveryOptions = null);

    /**
     * @return PaymentOptionsInterface
     */
    public function getPaymentOptions();

    /**
     * @param PaymentOptionsInterface $paymentOptions
     * @return CartInterface
     */
    public function setPaymentOptions(PaymentOptionsInterface $paymentOptions = null);

    /**
     * @param bool $flag
     * @return CartInterface
     */
    public function setPayed($flag = true);

    /**
     * @return bool
     */
    public function isPayed();

    /**
     * @return DateTime
     */
    public function getPayedAt();
}