<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Sylius\Bundle\AssortmentBundle\Entity\CustomizableProduct;
use Sylius\Bundle\InventoryBundle\Model\StockableInterface;

use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author marcsteiner
 * BaseProduct without Variants
 */
class BaseProduct extends CustomizableProduct implements StockableInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(type="integer", name="on_hand")
     * @Assert\NotBlank
     * @Assert\Min(0)
     */
    protected $onHand = 0;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="available_on_demand")
     */
    protected $availableOnDemand = false;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=2, precision=11)
     * @Assert\NotBlank
     */
    protected $price = 0.00;

    /**
     * @return int
     */
    public function getSku()
    {
        return $this->getId();
    }

    /**
     * Get price.
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set price.
     *
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * {@inheritdoc}
     */
    public function isInStock()
    {
        return 0 < $this->onHand;
    }

    /**
     * {@inheritdoc}
     */
    public function getOnHand()
    {
        return $this->onHand;
    }

    /**
     * {@inheritdoc}
     */
    public function setOnHand($onHand)
    {
        $this->onHand = $onHand;
        if (0 > $this->onHand) {
            $this->onHand = 0;
        }
    }

    /**
     * @return string
     */
    public function getInventoryName()
    {
        return $this->getName();
    }

    /**
     * @return bool
     */
    public function isAvailableOnDemand()
    {
        return $this->availableOnDemand;
    }

    /**
     * @param bool $availableOnDemand
     */
    public function setAvailableOnDemand($availableOnDemand)
    {
        $this->availableOnDemand = (bool)$availableOnDemand;
    }

    /**
     * @return string
     */
    public function __toString(){
        return (string)$this->getInventoryName();
    }
}
