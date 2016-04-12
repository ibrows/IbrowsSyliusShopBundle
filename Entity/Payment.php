<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Payment\PaymentInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_payment")
 * @ORM\InheritanceType("JOINED")
 */
class Payment implements PaymentInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var CartInterface
     * @ORM\ManyToOne(targetEntity="Ibrows\SyliusShopBundle\Model\CartInterface", inversedBy="payments")
     * @ORM\JoinColumn(name="cart_id", referencedColumnName="id")
     */
    protected $cart;

    /**
     * @var string
     * @ORM\Column(type="string", name="strategy_id", nullable=false)
     */
    protected $strategyId;

    /**
     * @var array
     * @ORM\Column(type="json_array", name="strategy_data", nullable=true)
     */
    protected $strategyData;

    /**
     * @var array
     * @ORM\Column(type="json_array", name="data", nullable=true)
     */
    protected $data;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return CartInterface
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param CartInterface $cart
     * @param bool          $stopPropagation
     *
     * @return $this|PaymentInterface
     */
    public function setCart(CartInterface $cart = null, $stopPropagation = false)
    {
        if (!$stopPropagation) {
            if (!is_null($this->cart)) {
                $this->cart->removePayment($this, true);
            }
            if (!is_null($cart)) {
                $cart->addPayment($this, true);
            }
        }
        $this->cart = $cart;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return $this|PaymentInterface
     */
    public function setAmount($amount = 0.0)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return $this|PaymentInterface
     */
    public function setData(array $data = null)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return string
     */
    public function getStrategyId()
    {
        return $this->strategyId;
    }

    /**
     * @param null $strategyId
     *
     * @return $this|PaymentInterface
     */
    public function setStrategyId($strategyId = null)
    {
        $this->strategyId = $strategyId;

        return $this;
    }

    /**
     * @return array
     */
    public function getStrategyData()
    {
        return $this->strategyData;
    }

    /**
     * @param array $strategyData
     *
     * @return $this|PaymentInterface
     */
    public function setStrategyData(array $strategyData = null)
    {
        $this->strategyData = $strategyData;

        return $this;
    }
}
