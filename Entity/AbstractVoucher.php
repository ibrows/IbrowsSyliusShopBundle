<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ibrows\SyliusShopBundle\Model\Voucher\BaseVoucherInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\MappedSuperclass
 * @DoctrineAssert\UniqueEntity(fields={"code"}, message="code.already.exist" )
 */
abstract class AbstractVoucher implements BaseVoucherInterface
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
     * @ORM\Column(type="string", unique=true)
     */
    protected $code;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", name="created_at")
     */
    protected $createdAt;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Voucher
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodeWithPrefix()
    {
        return static::getPrefix().$this->getCode();
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return Voucher
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}