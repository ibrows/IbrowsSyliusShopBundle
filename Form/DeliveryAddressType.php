<?php

namespace Ibrows\SyliusShopBundle\Form;

class DeliveryAddressType extends AbstractAddressType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'ibr_sylius_deliveryaddress';
    }
}