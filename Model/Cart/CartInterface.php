<?php

namespace Ibrows\SyliusShopBundle\Model\Cart;

use Doctrine\Common\Collections\Collection;

use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;

use Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface;
use Ibrows\SyliusShopBundle\Model\Delivery\DeliveryOptionInterface;
use Ibrows\SyliusShopBundle\Model\Payment\PaymentOptionsInterface;

use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;

use Sylius\Bundle\CartBundle\Model\CartInterface as BaseCartInterface;

use DateTime;

interface CartInterface extends BaseCartInterface
{
    /**
     * @param AdditionalCartItemInterface $item
     * @return CartInterface
     */
    public function addAddiationalItem(AdditionalCartItemInterface $item);
    /**
     * @param AdditionalCartItemInterface $item
     * @return CartInterface
     */
    public function removeAddiationalItem(AdditionalCartItemInterface $item);

    /**
     * @param Collection|AdditionalCartItemInterface[] $items
     * @return CartInterface
     */
    public function setAdditionalItems(Collection $items);

    /**
     * @return AdditionalCartItemInterface[]
     */
    public function getAdditionalItems();

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param string $email
     * @return CartInterface
     */
    public function setEmail($email = null);

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
     * @return InvoiceAddressInterface
     */
    public function getInvoiceAddressObj();

    /**
     * @param InvoiceAddressInterface $invoiceAddressObj
     * @return CartInterface
     */
    public function setInvoiceAddressObj(InvoiceAddressInterface $invoiceAddressObj = null);

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
     * @return DeliveryAddressInterface
     */
    public function getDeliveryAddressObj();

    /**
     * @param DeliveryAddressInterface $deliveryAddressObj
     * @return CartInterface
     */
    public function setDeliveryAddressObj(DeliveryAddressInterface $deliveryAddressObj = null);

    /**
     * @return DeliveryOptionInterface
     */
    public function getDeliveryOption();

    /**
     * @param DeliveryOptionInterface $DeliveryOption
     * @return CartInterface
     */
    public function setDeliveryOption(DeliveryOptionInterface $DeliveryOption = null);

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
     * @return PaymentInstructionInterface
     */
    public function getPaymentInstruction();

    /**
     * @param PaymentInstructionInterface $instruction
     * @return PaymentInstructionInterface
     */
    public function setPaymentInstruction(PaymentInstructionInterface $instruction = null);

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

    /**
     * @param bool $flag
     * @return CartInterface
     */
    public function setClosed($flag = true);

    /**
     * @return bool
     */
    public function isClosed();

    /**
     * @return DateTime
     */
    public function getClosedAt();

    /**
     * @return CartInterface
     */
    public function refreshCart();
}