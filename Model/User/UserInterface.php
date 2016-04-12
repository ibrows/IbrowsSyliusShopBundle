<?php

namespace Ibrows\SyliusShopBundle\Model\User;

use FOS\UserBundle\Model\UserInterface as FosUserInterface;
use Ibrows\SyliusShopBundle\Model\Address\AddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface;

interface UserInterface extends FosUserInterface
{
    /**
     * @return InvoiceAddressInterface
     */
    public function getInvoiceAddress();

    /**
     * @param InvoiceAddressInterface $invoiceAddress
     *
     * @return UserInterface
     */
    public function setInvoiceAddress(InvoiceAddressInterface $invoiceAddress = null);

    /**
     * @return DeliveryAddressInterface
     */
    public function getDeliveryAddress();

    /**
     * @return int
     */
    public function getId();

    /**
     * @param DeliveryAddressInterface $deliveryAddress
     *
     * @return UserInterface
     */
    public function setDeliveryAddress(DeliveryAddressInterface $deliveryAddress = null);

    /**
     * @param AddressInterface $addresss
     *
     * @return UserInterface
     */
    public function removeAddress(AddressInterface $addresss);

    /**
     * @param AddressInterface $addresss
     *
     * @return UserInterface
     */
    public function addAddress(AddressInterface $addresss);

    /**
     * @return AddressInterface[]
     */
    public function getAddresses();

    /**
     * @param AddressInterface[] $addresses
     *
     * @return AddressInterface
     */
    public function setAddresses($addresses);
}
