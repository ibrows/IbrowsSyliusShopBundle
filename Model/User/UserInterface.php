<?php

namespace Ibrows\SyliusShopBundle\Model\User;
use Doctrine\Common\Collections\Collection;
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
     * @return UserInterface
     */
    public function setDeliveryAddress(DeliveryAddressInterface $deliveryAddress = null);

    /**
     * @param AddressInterface $addresss
     */
    public function removeAddress(AddressInterface $addresss);

    /**
     * @param AddressInterface $addresss
     */
    public function addAddress(AddressInterface $addresss);

    /**
     * @return AddressInterface[]
     */
    public function getAddresses();

    /**
     * @param  $addresses
     */
    public function setAddresses($addresses);

}
