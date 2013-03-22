<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Ibrows\SyliusShopBundle\Model\Product\ProductInterface;

use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author marcsteiner
 * BaseProduct without Variants
 */
class BaseProduct implements ProductInterface
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
     * @var string
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected $slug;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * Product description.
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $description;

    /**
     * Available on.
     * @ORM\Column(type="datetime")
     * @var DateTime
     */
    protected $availableOn;

    /**
     * Meta keywords.
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $metaKeywords;

    /**
     * Meta description.
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $metaDescription;

    /**
     * Creation time.
     * @ORM\Column(type="datetime")
     * @var DateTime
     */
    protected $createdAt;

    /**
     * Last update time.
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTime
     */
    protected $updatedAt;

    /**
     * Deletion time.
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTime
     */
    protected $deletedAt;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->availableOn = new \DateTime('now');
        $this->createdAt = new \DateTime('now');
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * {@inheritdoc}
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        return new \DateTime('now') >= $this->availableOn;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableOn()
    {
        return $this->availableOn;
    }

    /**
     * {@inheritdoc}
     */
    public function setAvailableOn(\DateTime $availableOn)
    {
        $this->availableOn = $availableOn;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function isDeleted()
    {
        return null !== $this->deletedAt && new \DateTime('now') >= $this->deletedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setDeletedAt(\DateTime $deletedAt = null)
    {
        $this->deletedAt = $deletedAt;
    }
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
