<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Payment\PaymentInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_payment")
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
     * @var array $data
     * @ORM\Column(type="json_array", name="data", nullable=true)
     */
    protected $data;

    /**
     * {@inheritdoc}
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
     * @param bool $stopPropagation
     * @return $this
     */
    public function setCart(CartInterface $cart = null, $stopPropagation = false)
    {
        if(!$stopPropagation) {
            if(!is_null($this->cart)) {
                $this->cart->removePayment($this, true);
            }
            if(!is_null($cart)) {
                $cart->addPayment($this, true);
            }
        }
        $this->cart = $cart;
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
     * @return CartInterface
     */
    public function setData(array $data = null)
    {
        $this->data = $data;
        return $this;
    }
}