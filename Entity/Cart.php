<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

use Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface;
use Ibrows\SyliusShopBundle\Model\Delivery\DeliveryOptionsInterface;
use Ibrows\SyliusShopBundle\Model\Payment\PaymentOptionsInterface;

use Sylius\Bundle\CartBundle\Model\CartItemInterface;
use Sylius\Bundle\CartBundle\Entity\Cart as BaseCart;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_cart")
 */
class Cart extends BaseCart implements CartInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $email;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="CartItem", mappedBy="cart", cascade="all", orphanRemoval=true)
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id")
     */
    protected $items;

    /**
     * @var bool
     * @ORM\Column(type="DateTime", nullable=true)
     */
    protected $payed = null;

    /**
     * @var InvoiceAddressInterface
     */
    protected $invoiceAddress;

    /**
     * @var DeliveryAddressInterface
     */
    protected $deliveryAddress;

    /**
     * @var DeliveryOptionsInterface
     */
    protected $deliveryOptions;

    /**
     * @var PaymentOptionsInterface
     */
    protected $paymentOptions;

    /**
     * @return Cart
     */
    public function refreshCart(){
        $this->calculateTotal();
        $this->setTotalItems($this->countItems());
        return $this;
    }

    /**
     * @param CartItemInterface $item
     * @return Cart
     */
    public function addItem(CartItemInterface $item){
        parent::addItem($item);
        $this->refreshCart();
        return $this;
    }

    /**
     * @param CartItemInterface $item
     * @return Cart
     */
    public function removeItem(CartItemInterface $item){
        parent::removeItem($item);
        $this->refreshCart();
        return $this;
    }

    /**
     * @param Collection|CartItemInterface[] $items
     * @return Cart
     */
    public function setItems(Collection $items){
        foreach($this->items as $item){
            $this->removeItem($item);
        }
        foreach($items as $item){
            $this->addItem($item);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Cart
     */
    public function setEmail($email)
    {
        $this->email = $email;
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
     * @return Cart
     */
    public function setInvoiceAddress(InvoiceAddressInterface $invoiceAddress = null)
    {
        $this->invoiceAddress = $invoiceAddress;
        return $this;
    }

    /**
     * @return DeliveryAddressInterface
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * @param DeliveryAddressInterface $deliveryAddress
     * @return Cart
     */
    public function setDeliveryAddress(DeliveryAddressInterface $deliveryAddress = null)
    {
        $this->deliveryAddress = $deliveryAddress;
        return $this;
    }

    /**
     * @return DeliveryOptionsInterface
     */
    public function getDeliveryOptions()
    {
        return $this->deliveryOptions;
    }

    /**
     * @param DeliveryOptionsInterface $deliveryOptions
     * @return Cart
     */
    public function setDeliveryOptions(DeliveryOptionsInterface $deliveryOptions = null)
    {
        $this->deliveryOptions = $deliveryOptions;
        return $this;
    }

    /**
     * @return PaymentOptionsInterface
     */
    public function getPaymentOptions()
    {
        return $this->paymentOptions;
    }

    /**
     * @param PaymentOptionsInterface $paymentOptions
     * @return Cart
     */
    public function setPaymentOptions(PaymentOptionsInterface $paymentOptions = null)
    {
        $this->paymentOptions = $paymentOptions;
    }

    /**
     * @param bool $flag
     * @return Cart
     */
    public function setPayed($flag = true)
    {
        if(false === $flag){
            $this->payed = null;
        }else{
            $this->payed = new DateTime;
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isPayed()
    {
        return $this->payed !== null;
    }

    /**
     * @return DateTime
     */
    public function getPayedAt()
    {
        return $this->payed;
    }
}