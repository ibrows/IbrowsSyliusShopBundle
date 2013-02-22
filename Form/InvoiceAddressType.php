<?php

namespace Ibrows\SyliusShopBundle\Form;

class InvoiceAddressType extends AbstractAddressType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'ibr_sylius_invoiceaddress';
    }
}