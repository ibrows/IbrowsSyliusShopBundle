<?php

namespace Ibrows\SyliusShopBundle\Model\Cart;

use Doctrine\Common\Collections\Collection;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;
use Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Payment\PaymentInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartStrategyInterface;
use Sylius\Bundle\CartBundle\Model\CartInterface as BaseCartInterface;
use DateTime;

interface CartInterface extends BaseCartInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getCurrency();

    /**
     * @param string $currency
     * @return CartInterface
     */
    public function setCurrency($currency);

    /**
     * @param AdditionalCartItemInterface $item
     * @return CartInterface
     */
    public function addAdditionalItem(AdditionalCartItemInterface $item);

    /**
     * @return float
     */
    public function getAmountToPay();

    /**
     * @param float $amountToPay
     * @return CartInterface
     */
    public function setAmountToPay($amountToPay);

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
    public function setConfirmed($flag = true);

    /**
     * @return bool
     */
    public function isConfirmed();

    /**
     * @return DateTime
     */
    public function getConfirmedAt();

    /**
     * @param bool $flag
     * @return CartInterface
     */
    public function setCreated($flag = true);

    /**
     * @return DateTime
     */
    public function getCreatedAt();

    /**
     * @return bool
     */
    public function isCreated();

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
     * @param array $data
     * @return CartInterface
     */
    public function setDeliveryOptionStrategyServiceData(array $data = null);

    /**
     * @return array
     */
    public function getDeliveryOptionStrategyServiceData();

    /**
     * @param string $serviceId
     * @return CartInterface
     */
    public function setPaymentOptionStrategyServiceId($serviceId);

    /**
     * @return string
     */
    public function getPaymentOptionStrategyServiceId();

    /**
     * @param array $data
     * @return CartInterface
     */
    public function setPaymentOptionStrategyServiceData(array $data = null);

    /**
     * @return array
     */
    public function getPaymentOptionStrategyServiceData();

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
     * @return CartItemInterface[]
     */
    public function getItems();

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

    /**
     * @return float
     */
    public function getAdditionalItemsPriceTotalTax();

    /**
     * @param float $additionalItemsPriceTotalTax
     * @return CartInterface
     */
    public function setAdditionalItemsPriceTotalTax($additionalItemsPriceTotalTax);

    /**
     * @return float
     */
    public function getItemsPriceTotalTax();

    /**
     * @param float $itemsPriceTotalTax
     * @return CartInterface
     */
    public function setItemsPriceTotalTax($itemsPriceTotalTax);

    /**
     * @param integer $itemId
     * @return CartItemInterface
     */
    public function getItemById($itemId);

    /**
     * @return float
     */
    public function getTotalWithTax();

    /**
     * @param float $totalWithTax
     * @return CartInterface
     */
    public function setTotalWithTax($totalWithTax);

    /**
     * @return Payments[]
     */
    public function getPayments();

    /**
     * @param PaymentInterface $payment
     * @param bool $stopPropagation
     * @return $this
     */
    public function addPayment(PaymentInterface $payment, $stopPropagation = false);

    /**
     * @param PaymentInterface $payment
     * @param bool $stopPropagation
     * @return $this
     */
    public function removePayment(PaymentInterface $payment, $stopPropagation = false);

    /**
     * @param Collection|PaymentInterFace[] $payments
     * @return $this
     */
    public function setPayments(Collection $payments);
}