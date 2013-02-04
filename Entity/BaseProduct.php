<?php

namespace Ibrows\SyliusShopBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Bundle\AssortmentBundle\Entity\CustomizableProduct;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Bundle\InventoryBundle\Model\StockableInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author marcsteiner
 * BaseProduct without Variants
 */
class BaseProduct extends CustomizableProduct implements StockableInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     * @Assert\Min(0)
     */
    protected $onHand = 1;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $availableOnDemand = true;

    /**
     *
     * @Assert\NotBlank
     *
     * @var float
     */
    protected $price = 0.00;

    /**
     * Override constructor to set on hand stock.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function setDeletedAt(\DateTime $deletedAt = null)
    {
        $this->deletedAt = $deletedAt;
    }

    public function getSku()
    {
        $this->getId();
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

    public function getInventoryName()
    {
        return $this->getName();
    }

    public function isAvailableOnDemand()
    {
        return $this->availableOnDemand;
    }

    public function setAvailableOnDemand($availableOnDemand)
    {
        $this->availableOnDemand = (Boolean) $availableOnDemand;
    }

}
