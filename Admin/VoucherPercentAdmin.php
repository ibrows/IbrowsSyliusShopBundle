<?php

namespace Ibrows\SyliusShopBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class VoucherPercentAdmin extends DefaultAdmin
{
    /**
     * @param ListMapper $listMapper
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('code')
            ->add('percent')
            ->add('validFrom')
            ->add('validTo')
        ;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('code')
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('code')
            ->add('percent')
            ->add('validFrom', 'datetime', array(
                'date_widget' => 'single_text',
                'time_widget' => 'single_text'
            ))
            ->add('validTo', 'datetime', array(
                'date_widget' => 'single_text',
                'time_widget' => 'single_text'
            ))
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    public function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('code')
            ->add('percent')
            ->add('validFrom')
            ->add('validTo')
        ;
    }
}