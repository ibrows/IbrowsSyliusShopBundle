<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Payment\PaymentInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Strategy\CartStrategyInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;
use Sylius\Bundle\CartBundle\Model\CartItemInterface as SyliusCartItemInterface;
use Sylius\Bundle\CartBundle\Entity\Cart as BaseCart;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_cart")
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(name="total",
 *          column=@ORM\Column(
 *              name = "total",
 *              type = "decimal",
 *              scale = 30,
 *              precision = 65
 *          )
 *      )
 * })
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
     * @var string
     * @ORM\Column(type="string", name="delivery_option_strategy_service_id", nullable=true)
     */
    protected $deliveryOptionStrategyServiceId;

    /**
     * @var array $deliveryOptionStrategyServiceData
     * @ORM\Column(type="json_array", name="delivery_option_strategy_service_data", nullable=true)
     */
    protected $deliveryOptionStrategyServiceData;

    /**
     * @var string
     * @ORM\Column(type="string", name="payment_option_strategy_service_id", nullable=true)
     */
    protected $paymentOptionStrategyServiceId;

    /**
     * @var array $paymentOptionStrategyServiceData
     * @ORM\Column(type="json_array", name="payment_option_strategy_service_data", nullable=true)
     */
    protected $paymentOptionStrategyServiceData;

    /**
     * @var Collection|CartItemInterface[]
     * @ORM\OneToMany(targetEntity="Ibrows\SyliusShopBundle\Model\CartItemInterface", mappedBy="cart", cascade="all", orphanRemoval=true)
     */
    protected $items;

    /**
     * @var Collection|AdditionalCartItemInterface[]
     * @ORM\OneToMany(targetEntity="Ibrows\SyliusShopBundle\Model\AdditionalCartItemInterface", mappedBy="cart", cascade="all", orphanRemoval=true)
     */
    protected $additionalItems;

    /**
     * @var int
     * @ORM\Column(type="integer", name="total_additional_items")
     */
    protected $totalAdditionalItems = 0;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=30, precision=65, name="total_with_tax")
     */
    protected $totalWithTax = 0.0;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=30, precision=65, name="additional_items_price_total")
     */
    protected $additionalItemsPriceTotal = 0.0;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=30, precision=65, name="additional_items_price_total_with_tax")
     */
    protected $additionalItemsPriceTotalWithTax = 0.0;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=30, precision=65, name="items_price_total")
     */
    protected $itemsPriceTotal = 0.0;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=30, precision=65, name="items_price_total_with_tax")
     */
    protected $itemsPriceTotalWithTax = 0.0;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=30, precision=65, name="items_price_total_tax")
     */
    protected $itemsPriceTotalTax = 0.0;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=30, precision=65, name="additional_items_price_total_tax")
     */
    protected $additionalItemsPriceTotalTax = 0.0;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=30, precision=65, name="total_tax")
     */
    protected $totalTax = 0.0;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", name="payed_at", nullable=true)
     */
    protected $payedAt;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", name="confirmed_at", nullable=true)
     */
    protected $confirmedAt;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", name="closed_at", nullable=true)
     */
    protected $closedAt;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", name="created_at")
     */
    protected $createdAt;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", name="terms_and_conditions_at", nullable=true)
     * @Assert\NotNull(groups={"sylius_wizard_summary"})
     */
    protected $termsAndConditionsAt;

    /**
     * @var InvoiceAddressInterface
     * @ORM\ManyToOne(targetEntity="Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface")
     * @ORM\JoinColumn(name="invoice_address_id", referencedColumnName="id")
     */
    protected $invoiceAddress;

    /**
     * @var InvoiceAddressInterface $invoiceAddressObj
     * @ORM\Column(type="object", name="invoice_address_obj", nullable=true)
     */
    protected $invoiceAddressObj;

    /**
     * @var DeliveryAddressInterface
     * @ORM\ManyToOne(targetEntity="Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface")
     * @ORM\JoinColumn(name="delivery_address_id", referencedColumnName="id")
     */
    protected $deliveryAddress;

    /**
     * @var InvoiceAddressInterface $deliveryAddressObj
     * @ORM\Column(type="object", name="delivery_address_obj", nullable=true)
     */
    protected $deliveryAddressObj;

    /**
     * @var Collection|PaymentInterface[]
     * @ORM\OneToMany(targetEntity="Ibrows\SyliusShopBundle\Model\Cart\Payment\PaymentInterface", mappedBy="cart", cascade="all", orphanRemoval=true)
     */
    protected $payments;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=30, precision=65, name="amount_to_pay")
     */
    protected $amountToPay = 0.0;

    public function __construct()
    {
        parent::__construct();
        $this->payments = new ArrayCollection();
        $this->items = new ArrayCollection();
        $this->additionalItems = new ArrayCollection();
        $this->setCreated();
    }

    /**
     * @return Cart
     */
    public function calculateTotal()
    {
        $itemsPriceTotal = 0.0;
        $itemsPriceTotalWithTax = 0.0;
        $itemsPriceTotalTax = 0.0;
        foreach ($this->items as $item) {
            $item->calculateTotal();
            $itemsPriceTotal += $item->getTotal();
            $itemsPriceTotalWithTax += $item->getTotalWithTaxPrice();
            $itemsPriceTotalTax += $item->getTaxPrice();
        }
        $this->setItemsPriceTotal($itemsPriceTotal);
        $this->setItemsPriceTotalWithTax($itemsPriceTotalWithTax);
        $this->setItemsPriceTotalTax($itemsPriceTotalTax);

        $additionalItemsPriceTotal = 0.0;
        $additionalItemsPriceTotalWithTax = 0.0;
        $additionalItemsPriceTax = 0.0;
        foreach ($this->additionalItems as $item) {
            $item->calculateTotal();
            $additionalItemsPriceTotal += $item->getPrice();
            $additionalItemsPriceTotalWithTax += $item->getPriceWithTax();
            $additionalItemsPriceTax += $item->getTaxPrice();
        }
        $this->setAdditionalItemsPriceTotal($additionalItemsPriceTotal);
        $this->setAdditionalItemsPriceTotalWithTax($additionalItemsPriceTotalWithTax);
        $this->setAdditionalItemsPriceTotalTax($additionalItemsPriceTax);

        $this->setTotalTax($this->getItemsPriceTotalTax() + $this->getAdditionalItemsPriceTotalTax());

        $this->setTotal($itemsPriceTotal + $additionalItemsPriceTotal);
        $this->setTotalWithTax($itemsPriceTotalWithTax + $additionalItemsPriceTotalWithTax);

        return $this;
    }

    /**
     * @return Cart
     */
    public function refreshCart(){
        $this->calculateTotal();
        $this->setTotalItems($this->countItems());
        $this->setTotalAdditionalItems($this->countAdditionalItems());
        return $this;
    }

    /**
     * @param AdditionalCartItemInterface $item
     * @return Cart
     */
    public function addAdditionalItem(AdditionalCartItemInterface $item){
        $this->additionalItems->add($item);
        $item->setCart($this);
        $this->refreshCart();
        return $this;
    }

    /**
     * @param AdditionalCartItemInterface $item
     * @return Cart
     */
    public function removeAdditionalItem(AdditionalCartItemInterface $item){
        $this->additionalItems->removeElement($item);
        $item->setCart(null);
        $this->refreshCart();
        return $this;
    }

    /**
     * @param SyliusCartItemInterface $item
     * @return $this
     */
    public function addItem(SyliusCartItemInterface $item)
    {
        parent::addItem($item);
        $this->refreshCart();
        return $this;
    }

    /**
     * @param SyliusCartItemInterface $item
     * @return $this
     */
    public function removeItem(SyliusCartItemInterface $item)
    {
        parent::removeItem($item);
        $this->refreshCart();
        return $this;
    }

    /**
     * @param CartStrategyInterface $strategy
     * @return AdditionalCartItemInterface[]
     */
    public function getAdditionalItemsByStrategy(CartStrategyInterface $strategy)
    {
        $items = array();
        foreach($this->additionalItems as $item){
            if($item->getStrategyIdentifier() == $strategy->getServiceId()){
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * @return Collection|AdditionalCartItemInterface[]
     */
    public function getAdditionalItems()
    {
        return $this->additionalItems;
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
        $this->refreshCart();
        return $this;
    }

    /**
     * @param Collection|AdditionalCartItemInterface[] $items
     * @return Cart
     */
    public function setAdditionalItems(Collection $items){
        foreach($this->additionalItems as $item){
            $this->removeAdditionalItem($item);
        }
        foreach($items as $item){
            $this->addAdditionalItem($item);
        }
        $this->refreshCart();
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
     * @param bool $flag
     * @return Cart
     */
    public function setPayed($flag = true)
    {
        if(false === $flag){
            $this->payedAt = null;
        }else{
            $this->payedAt = new DateTime;
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isPayed()
    {
        return $this->payedAt !== null;
    }

    /**
     * @return DateTime
     */
    public function getPayedAt()
    {
        return $this->payedAt;
    }

    /**
     * @return DateTime
     */
    public function isClosed()
    {
        return $this->closedAt;
    }

    /**
     * @param bool $flag
     * @return Cart
     */
    public function setClosed($flag = true)
    {
        if(false === $flag){
            $this->closedAt = null;
        }else{
            $this->closedAt = new DateTime;
        }
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getClosedAt()
    {
        return $this->closedAt;
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
     * @return InvoiceAddressInterface
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
        return $this->termsAndConditionsAt !== null;
    }

    /**
     * @return DateTime
     */
    public function getTermsAndConditionsAt()
    {
        return $this->termsAndConditionsAt;
    }

    /**
     * @param bool $flag
     * @return Cart
     */
    public function setTermsAndConditions($flag = true)
    {
        if(false === $flag){
            $this->termsAndConditionsAt = null;
        }else{
            $this->termsAndConditionsAt = new DateTime;
        }
        return $this;
    }

    /**
     * @param string $serviceId
     * @return Cart
     */
    public function setDeliveryOptionStrategyServiceId($serviceId)
    {
        $this->deliveryOptionStrategyServiceId = $serviceId;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryOptionStrategyServiceId()
    {
        return $this->deliveryOptionStrategyServiceId;
    }

    /**
     * @return int
     */
    public function getTotalAdditionalItems()
    {
        return $this->totalAdditionalItems;
    }

    /**
     * @param int $totalAdditionalItems
     * @return Cart
     */
    public function setTotalAdditionalItems($totalAdditionalItems)
    {
        $this->totalAdditionalItems = $totalAdditionalItems;
        return $this;
    }

    /**
     * @return int
     */
    protected function countAdditionalItems()
    {
        return count($this->additionalItems);
    }

    /**
     * @param float $total
     * @return CartInterface
     */
    public function setItemsPriceTotal($total)
    {
        $this->itemsPriceTotal = $total;
        return $this;
    }

    /**
     * @return float
     */
    public function getItemsPriceTotal()
    {
        return $this->itemsPriceTotal;
    }

    /**
     * @param float $total
     * @return CartInterface
     */
    public function setAdditionalItemsPriceTotal($total)
    {
        $this->additionalItemsPriceTotal = $total;
        return $this;
    }

    /**
     * @return float
     */
    public function getAdditionalItemsPriceTotal()
    {
        return $this->additionalItemsPriceTotal;
    }

    /**
     * @return float
     */
    public function getAdditionalItemsPriceTotalWithTax()
    {
        return $this->additionalItemsPriceTotalWithTax;
    }

    /**
     * @param float $additionalItemsPriceTotalWithTax
     * @return Cart
     */
    public function setAdditionalItemsPriceTotalWithTax($additionalItemsPriceTotalWithTax)
    {
        $this->additionalItemsPriceTotalWithTax = $additionalItemsPriceTotalWithTax;
        return $this;
    }

    /**
     * @return float
     */
    public function getItemsPriceTotalWithTax()
    {
        return $this->itemsPriceTotalWithTax;
    }

    /**
     * @param float $itemsPriceTotalWithTax
     * @return Cart
     */
    public function setItemsPriceTotalWithTax($itemsPriceTotalWithTax)
    {
        $this->itemsPriceTotalWithTax = $itemsPriceTotalWithTax;
        return $this;
    }

    /**
     * @return float
     */
    public function getAdditionalItemsPriceTotalTax()
    {
        return $this->additionalItemsPriceTotalTax;
    }

    /**
     * @param float $additionalItemsPriceTotalTax
     * @return Cart
     */
    public function setAdditionalItemsPriceTotalTax($additionalItemsPriceTotalTax)
    {
        $this->additionalItemsPriceTotalTax = $additionalItemsPriceTotalTax;
        return $this;
    }

    /**
     * @return float
     */
    public function getItemsPriceTotalTax()
    {
        return $this->itemsPriceTotalTax;
    }

    /**
     * @param float $itemsPriceTotalTax
     * @return Cart
     */
    public function setItemsPriceTotalTax($itemsPriceTotalTax)
    {
        $this->itemsPriceTotalTax = $itemsPriceTotalTax;
        return $this;
    }

    /**
     * @param integer $itemId
     * @return CartItemInterface
     */
    public function getItemById($itemId)
    {
        foreach($this->items as $item){
            if($item->getId() == $itemId){
                return $item;
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getDeliveryOptionStrategyServiceData()
    {
        return $this->deliveryOptionStrategyServiceData;
    }

    /**
     * @param array $data
     * @return Cart
     */
    public function setDeliveryOptionStrategyServiceData(array $data = null)
    {
        $this->deliveryOptionStrategyServiceData = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getPaymentOptionStrategyServiceData()
    {
        return $this->paymentOptionStrategyServiceData;
    }

    /**
     * @param array $data
     * @return Cart
     */
    public function setPaymentOptionStrategyServiceData(array $data = null)
    {
        $this->paymentOptionStrategyServiceData = $data;
        return $this;
    }

    /**
     * @param string $serviceId
     * @return CartInterface
     */
    public function setPaymentOptionStrategyServiceId($serviceId)
    {
        $this->paymentOptionStrategyServiceId = $serviceId;
    }

    /**
     * @return string
     */
    public function getPaymentOptionStrategyServiceId()
    {
        return $this->paymentOptionStrategyServiceId;
    }

    /**
     * @return float
     */
    public function getTotalWithTax()
    {
        return $this->totalWithTax;
    }

    /**
     * @param float $totalWithTax
     * @return Cart
     */
    public function setTotalWithTax($totalWithTax)
    {
        $this->totalWithTax = $totalWithTax;
        return $this;
    }

    /**
     * @return float
     */
    public function getTotalTax()
    {
        return $this->totalTax;
    }

    /**
     * @param float $totalTax
     * @return Cart
     */
    public function setTotalTax($totalTax)
    {
        $this->totalTax = $totalTax;
        return $this;
    }

    /**
     * @param bool $flag
     * @return CartInterface
     */
    public function setConfirmed($flag = true)
    {
        if(false === $flag){
            $this->confirmedAt = null;
        }else{
            $this->confirmedAt = new DateTime;
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isConfirmed()
    {
        return null !== $this->confirmedAt;
    }

    /**
     * @return DateTime
     */
    public function getConfirmedAt()
    {
        return $this->confirmedAt;
    }

    /**
     * @return bool
     */
    public function isCreated()
    {
        return $this->createdAt !== null;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param bool $flag
     * @return Cart
     */
    public function setCreated($flag = true)
    {
        if(false === $flag){
            $this->createdAt = null;
        }else{
            $this->createdAt = new DateTime;
        }
        return $this;
    }

    /**
     * @param PaymentInterface $payment
     * @param bool $stopPropagation
     * @return $this
     */
    public function addPayment(PaymentInterface $payment, $stopPropagation = false)
    {
        $this->payments->add($payment);
        if(!$stopPropagation) {
            $payment->setCart($this, true);
        }

        return $this;
    }

    /**
     * @param PaymentInterface $payment
     * @param bool $stopPropagation
     * @return $this
     */
    public function removePayment(PaymentInterface $payment, $stopPropagation = false)
    {
        $this->payments->removeElement($payment);
        if(!$stopPropagation) {
            $payment->setCart(null, true);
        }

        return $this;
    }

    /**
     * @param Collection|Paymentinterface[] $payments
     * @return $this
     */
    public function setPayments(Collection $payments)
    {
        foreach($this->payments as $payment) {
            $this->removePayment($payment);
        }
        foreach($payments as $payment) {
            $this->addPayment($payment);
        }

        return $this;
    }

    /**
     * @return PaymentInterface[]
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @return float
     */
    public function getAmountToPay()
    {
        return $this->amountToPay;
    }

    /**
     * @param float $amountToPay
     * @return Cart
     */
    public function setAmountToPay($amountToPay)
    {
        $this->amountToPay = $amountToPay;
        return $this;
    }
}