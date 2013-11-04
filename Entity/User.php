<?php

namespace Ibrows\SyliusShopBundle\Entity;

use FOS\UserBundle\Model\User as FOSUser;
use Doctrine\ORM\Mapping as ORM;
use Ibrows\SyliusShopBundle\Model\Address\AddressInterface;
use Ibrows\SyliusShopBundle\Model\User\UserInterface;
use Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\MappedSuperclass
 * @ORM\Table(name="fos_user")
 */
abstract class User extends FOSUser implements UserInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var InvoiceAddressInterface
     * @ORM\OneToOne(targetEntity="Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface")
     * @ORM\JoinColumn(name="invoice_address_id", referencedColumnName="id")
     */
    protected $invoiceAddress;

    /**
     * @var DeliveryAddressInterface
     * @ORM\OneToOne(targetEntity="Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface")
     * @ORM\JoinColumn(name="delivery_address_id", referencedColumnName="id")
     */
    protected $deliveryAddress;

    /**
     * @var AddressInterface[]
     * @ORM\OneToMany(targetEntity="Ibrows\SyliusShopBundle\Model\Address\AddressInterface", mappedBy="user")
     */
    protected $addresses;

    public function __construct(){
        parent::__construct();
        $this->addresses = new ArrayCollection();
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        parent::setEmail($email);
        $this->setUsername($email);
        return $this;
    }

    /**
     * @return InvoiceAddressInterface
     */
    public function getInvoiceAddress()
    {
        return $this->invoiceAddress;
    }

    /**
     * @param InvoiceAddressInterface $invoiceAddress
     * @return $this
     */
    public function setInvoiceAddress(InvoiceAddressInterface $invoiceAddress = null)
    {
        $this->invoiceAddress = $invoiceAddress;
        $this->addAddress($invoiceAddress);
        return $this;
    }

    /**
     * @return \Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * @param DeliveryAddressInterface $deliveryAddress
     * @return $this
     */
    public function setDeliveryAddress(DeliveryAddressInterface $deliveryAddress = null)
    {
        $this->deliveryAddress = $deliveryAddress;
        $this->addAddress($deliveryAddress);
        return $this;
    }

    /**
     * @param AddressInterface $addresss
     * @param bool $stopPropagation
     * @return $this
     */
    public function addAddress(AddressInterface $addresss, $stopPropagation = false)
    {
        $this->addresses->add($addresss);
        if (!$stopPropagation) {
            $addresss->setUser($this, true);
        }
        return $this;
    }

    /**
     * @param AddressInterface $address
     * @param bool $stopPropagation
     * @return $this
     */
    public function removeAddress(AddressInterface $address, $stopPropagation = false)
    {
        $this->addresses->removeElement($address);
        if (!$stopPropagation) {
            $address->setUser(null, true);
        }
        return $this;
    }

    /**
     * @return AddressInterface[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param AddressInterface[] $addresses
     * @return $this
     */
    public function setAddresses($addresses)
    {
        foreach ($this->addresses as $address) {
            $this->removeAddress($address);
        }
        foreach ($this->addresses as $address) {
            $this->addAddress($address);
        }
        $this->addresses = $addresses;
        return $this;
    }

}
