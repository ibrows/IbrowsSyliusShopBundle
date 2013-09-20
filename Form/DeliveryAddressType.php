<?php

namespace Ibrows\SyliusShopBundle\Form;

class DeliveryAddressType extends AddressType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'ibr_sylius_deliveryaddress';
    }
}