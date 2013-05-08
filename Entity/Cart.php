<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface;
use Ibrows\SyliusShopBundle\Model\Delivery\DeliveryOptionInterface;
use Ibrows\SyliusShopBundle\Model\Payment\PaymentOptionsInterface;
use Sylius\Bundle\CartBundle\Model\CartItemInterface;
use Sylius\Bundle\CartBundle\Entity\Cart as BaseCart;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_cart")
 */
class Cart extends BaseCart implements CartInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $currency;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $email;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="Ibrows\SyliusShopBundle\Model\CartItemInterface", mappedBy="cart", cascade="all", orphanRemoval=true)
     */
    protected $items;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="Ibrows\SyliusShopBundle\Model\AdditionalCartItemInterface", mappedBy="cart", cascade="all", orphanRemoval=true)
     */
    protected $additionalitems;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $payed;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $closed;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\NotNull(groups={"sylius_wizard_summary"})
     */
    protected $termsAndConditions;

    /**
     * @var InvoiceAddressInterface
     * @ORM\OneToOne(targetEntity="Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface")
     * @ORM\JoinColumn(name="invoice_address_id", referencedColumnName="id")
     */
    protected $invoiceAddress;

    /**
     * @var InvoiceAddressInterface $invoiceAddressObj
     * @ORM\Column(type="object", name="invoice_address_obj")
     */
    protected $invoiceAddressObj;

    /**
     * @var DeliveryAddressInterface
     * @ORM\OneToOne(targetEntity="Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface")
     * @ORM\JoinColumn(name="delivery_address_id", referencedColumnName="id")
     */
    protected $deliveryAddress;

    /**
     * @var InvoiceAddressInterface $deliveryAddressObj
     * @ORM\Column(type="object", name="delivery_address_obj")
     */
    protected $deliveryAddressObj;

    /**
     * @var DeliveryOptionInterface
     * @ORM\OneToOne(targetEntity="Ibrows\SyliusShopBundle\Model\Delivery\DeliveryOptionInterface")
     * @ORM\JoinColumn(name="delivery_options_id", referencedColumnName="id")
     */
    protected $deliveryOption;

    /**
     * @var PaymentOptionsInterface
     * @ORM\OneToOne(targetEntity="Ibrows\SyliusShopBundle\Model\Payment\PaymentOptionsInterface")
     * @ORM\JoinColumn(name="payment_options_id", referencedColumnName="id")
     */
    protected $paymentOptions;

    /**
     * @var PaymentInstructionInterface
     * @ORM\OneToOne(targetEntity="JMS\Payment\CoreBundle\Model\PaymentInstructionInterface")
     * @ORM\JoinColumn(name="payment_instructions_id", referencedColumnName="id")
     */
    protected $paymentInstruction;

    public function calculateTotal()
    {
        $this->total = 0.0;
        foreach ($this->items as $item) {
            $item->calculateTotal();
            $this->total += $item->getTotal();
        }
        foreach ($this->additionalitems as $item) {
            $this->total += $item->getPrice();
        }
    }

    /**
     * @return Cart
     */
    public function refreshCart(){
        $this->calculateTotal();
        $this->setTotalItems($this->countItems());
        return $this;
    }

    /**
     * @param AdditionalCartItemInterface $item
     * @return Cart
     */
    public function addAddiationalItem(AdditionalCartItemInterface $item){
        if($item instanceof DeliveryOptionInterface){
            $this->setDeliveryOption($item);
        }
        $this->additionalitems->add($item);
        $this->refreshCart();
        return $this;
    }

    /**
     * @param AdditionalCartItemInterface $item
     * @return Cart
     */
    public function removeAddiationalItem(AdditionalCartItemInterface $item){
        if($item instanceof DeliveryOptionInterface){
            $this->deliveryOption = null;
        }
        $this->additionalitems->remove($item);
        $this->refreshCart();
        return $this;
    }

    /**
     * @param Collection|AdditionalCartItemInterface[] $items
     * @return Cart
     */
    public function setAdditionalItems(Collection $items){
        foreach($this->items as $item){
            $this->removeItem($item);
        }
        foreach($items as $item){
            $this->addItem($item);
        }
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdditionalItems()
    {
        return $this->additionalitems;
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
    public function setEmail($email = null)
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
     * @return DeliveryOptionInterface
     */
    public function getDeliveryOption()
    {
        return $this->deliveryOption;
    }

    /**
     * @param DeliveryOptionInterface $deliveryOption
     * @return CartInterface
     */
    public function setDeliveryOption(DeliveryOptionInterface $deliveryOption = null)
    {
        if($deliveryOption == null && $this->deliveryOption != null){
            //remove current
            $this->removeAddiationalItem($item);
        }
        $this->deliveryOption = $deliveryOption;
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
     * @return PaymentInstructionInterface
     */
    public function getPaymentInstruction()
    {
        return $this->paymentInstruction;
    }

    /**
     * @param PaymentInstructionInterface $instruction
     * @return PaymentInstructionInterface
     */
    public function setPaymentInstruction(PaymentInstructionInterface $instruction = null)
    {
        $this->paymentInstruction = $instruction;
        return $this;
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

    /**
     * @return DateTime
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * @param bool $flag
     * @return Cart
     */
    public function setClosed($flag = true)
    {
        if(false === $flag){
            $this->closed = null;
        }else{
            $this->closed = new DateTime;
        }
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getClosedAt()
    {
        return $this->closed;
    }

    /**
     * @param InvoiceAddressInterface $invoiceAddressObj
     * @return CartInterface
     */
    public function setInvoiceAddressObj(InvoiceAddressInterface $invoiceAddressObj = null)
    {
        $this->invoiceAddressObj = $invoiceAddressObj;
        return $this;
    }

    /**
     * Get invoiceAddressObj
     *
     * @return \stdClass
     */
    public function getInvoiceAddressObj()
    {
        return $this->invoiceAddressObj;
    }

    /**
     * @param DeliveryAddressInterface $deliveryAddressObj
     * @return CartInterface
     */
    public function setDeliveryAddressObj(DeliveryAddressInterface $deliveryAddressObj = null)
    {
        $this->deliveryAddressObj = $deliveryAddressObj;
        return $this;
    }

    /**
     * Get deliveryAddressObj
     * @return \stdClass
     */
    public function getDeliveryAddressObj()
    {
        return $this->deliveryAddressObj;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return Cart
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function isTermsAndConditions()
    {
        return $this->termsAndConditions !== null;
    }

    /**
     * @return DateTime
     */
    public function getTermsAndConditionsAt()
    {
        return $this->termsAndConditions;
    }

    /**
     * @param bool $flag
     * return Cart
     */
    public function setTermsAndConditions($flag = true)
    {
        if(false === $flag){
            $this->termsAndConditions = null;
        }else{
            $this->termsAndConditions = new DateTime;
        }
        return $this;
    }
}