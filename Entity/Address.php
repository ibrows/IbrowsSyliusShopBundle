<?php

namespace Ibrows\SyliusShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ibrows\Bundle\SonataAdminAnnotationBundle\Annotation as Sonata;
use Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\Address as BaseAddress;

/**
 * @ORM\Entity
 * @ORM\Table(name="ibr_sylius_address")
 * @Sonata\Order\FormMapperAll
 * @Sonata\Order\ShowMapperAll
 * @ORM\InheritanceType("JOINED")
 */
class Address extends BaseAddress implements InvoiceAddressInterface, DeliveryAddressInterface
{
}
