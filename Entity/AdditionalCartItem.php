<?php

namespace Ibrows\SyliusShopBundle\Entity;
use Ibrows\SyliusShopBundle\Model\Cart\AdditionalCartItemInterface;

use Ibrows\SyliusShopBundle\Model\Product\ProductInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartItemInterface;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Sylius\Bundle\CartBundle\Entity\CartItem as BaseCartItem;
use Sylius\Bundle\CartBundle\Model\CartItemInterface as BaseCartItemInterface;

use Doctrine\ORM\Mapping as ORM;

use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_cart_additional_item")
 */
class AdditionalCartItem implements AdditionalCartItemInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var CartInterface
     * @ORM\ManyToOne(targetEntity="Ibrows\SyliusShopBundle\Model\CartInterface", inversedBy="additionalItems")
     */
    protected $cart;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=2, precision=11)
     */
    protected $price;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $text;

    /**
     * @var string
     * @ORM\Column(type="string", name="strategy_identifier")
     */
    protected $strategyIdentifier;

    /**
     * @var array
     * @ORM\Column(type="json_array", name="strategy_data")
     */
    protected $strategyData = array();

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getText();
    }

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
     * @return CartInterface
     */
    public function setCart(CartInterface $cart = null)
    {
        $this->cart = $cart;
        return $this;
    }

    /**
     * @return string
     */
    public function getStrategyIdentifier()
    {
        return $this->strategyIdentifier;
    }

    /**
     * @param string $identifier
     * @return AdditionalCartItem
     */
    public function setStrategyIdentifier($identifier)
    {
        $this->strategyIdentifier = $identifier;
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
     * @param array $data
     * @return AdditionalCartItem
     */
    public function setStrategyData(array $data = array())
    {
        $this->strategyData = $data;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return AdditionalCartItem
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return AdditionalCartItem
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }
}
