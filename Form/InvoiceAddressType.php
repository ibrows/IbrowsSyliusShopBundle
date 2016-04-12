<?php

namespace Ibrows\SyliusShopBundle\Form;

class InvoiceAddressType extends AddressType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'ibr_sylius_invoiceaddress';
    }
}
