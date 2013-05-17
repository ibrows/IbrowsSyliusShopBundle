<?php

namespace Ibrows\SyliusShopBundle\Model\Cart;

use Doctrine\Common\Collections\Collection;

use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;

use Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartStrategyInterface;
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
    public function addAdditionalItem(AdditionalCartItemInterface $item);

    /**
     * @param AdditionalCartItemInterface $item
     * @return CartInterface
     */
    public function removeAdditionalItem(AdditionalCartItemInterface $item);

    /**
     * @param Collection $items
     * @return CartInterface
     */
    public function setAdditionalItems(Collection $items);

    /**
     * @return int
     */
    public function getTotalAdditionalItems();

    /**
     * @param int $total
     * @return CartInterface
     */
    public function setTotalAdditionalItems($total);

    /**
     * @return AdditionalCartItemInterface[]
     */
    public function getAdditionalItems();

    /**
     * @param CartStrategyInterface $strategy
     * @return AdditionalCartItemInterface[]
     */
    public function getAdditionalItemsByStrategy(CartStrategyInterface $strategy);

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

    /**
     * @return bool
     */
    public function isTermsAndConditions();

    /**
     * @return DateTime
     */
    public function getTermsAndConditionsAt();

    /**
     * @param bool $flag
     * @return CartInterface
     */
    public function setTermsAndConditions($flag = true);

    /**
     * @param string $serviceId
     * @return CartInterface
     */
    public function setDeliveryOptionStrategyServiceId($serviceId);

    /**
     * @return string
     */
    public function getDeliveryOptionStrategyServiceId();

    /**
     * @param string $serviceId
     * @return CartInterface
     */
    public function setPaymentStrategyServiceId($serviceId);

    /**
     * @return string
     */
    public function getPaymentStrategyServiceId();

    /**
     * @param float $total
     * @return CartInterface
     */
    public function setItemsPriceTotal($total);

    /**
     * @return float
     */
    public function getItemsPriceTotal();

    /**
     * @param float $total
     * @return CartInterface
     */
    public function setAdditionalItemsPriceTotal($total);

    /**
     * @return float
     */
    public function getAdditionalItemsPriceTotal();

    /**
     * @param float $total
     * @return CartInterface
     */
    public function setItemsPriceTotalWithTax($total);

    /**
     * @return float
     */
    public function getItemsPriceTotalWithTax();

    /**
     * @param float $total
     * @return CartInterface
     */
    public function setAdditionalItemsPriceTotalWithTax($total);

    /**
     * @return CartInterface
     */
    public function getAdditionalItemsPriceTotalWithTax();
}