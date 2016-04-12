<?php

namespace Ibrows\SyliusShopBundle\Admin;

use Ibrows\Bundle\SonataAdminAnnotationBundle\Admin\AbstractSonataAdminAnnotationAdmin;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class DefaultAdmin extends AbstractSonataAdminAnnotationAdmin
{
    protected $translationDomain = 'Admin';

    protected function addDefaults($mapper, $search = 'get', $excludes = array(
            'deletedat',
    ))
    {
        $class = $this->getClass();
        $meta = $this->getModelManager()->getMetadata($class);
        $names = $meta->getFieldNames();
        $lownames = array_map('strtolower', $names);
        $methods = get_class_methods($class);
        foreach ($methods as $method) {
            if (stripos($method, $search) !== 0) {
                if ($search == 'get' && stripos($method, 'is') === 0) {
                    $method = substr($method, 2);
                } else {
                    continue;
                }
            } else {
                $method = substr($method, 3);
                $method = lcfirst($method);
            }
            $lowmethod = strtolower($method);
            if (in_array($lowmethod, $excludes)) {
                continue;
            }

            if (array_search($lowmethod, $lownames) !== false) {
                $mapper->remove($method);//be sure don't add twice
                $mapper->add($method);
            }
        }
    }

    protected function addActions($listMapper, array $actions = array(
            'show' => array(),
            'edit' => array(),
            'delete' => array(),
    ))
    {
        $listMapper->add('_action', 'actions', array('actions' => $actions));
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        if ($this->hasAnnotations()) {
            parent::configureListFields($listMapper);
        } else {
            $this->addDefaults($listMapper);
            $this->addActions($listMapper);
        }
    }

    private function hasAnnotations()
    {
        return sizeof($this->getSonataAnnotationReader()->getFormMapperAnnotations($this->getClass())) > 0;
    }

    protected function configureFormFields(FormMapper $form)
    {
        if ($this->hasAnnotations()) {
            parent::configureFormFields($form);
        } else {
            $this->addDefaults($form, 'set');
        }
    }

    protected function configureShowField(\Sonata\AdminBundle\Show\ShowMapper $showMapper)
    {
        if ($this->hasAnnotations()) {
            parent::configureShowField($showMapper);
        } else {
            $this->addDefaults($showMapper);
        }
    }

    public function getFormData(FormMapper $formMapper)
    {
        $data = null;
        if (!$data) {
            $data = $this->configurationPool->getContainer()->get('request')->get($this->getIdParameter());
        }
        if (!$data) {
            $data = ''.intval($this->configurationPool->getContainer()->get('request')->get('objectId'));
        }
        if (!$data) {
            $data = $this->getSubject();
        }
        if (!$data) {
            $data = $formMapper->getFormBuilder()->getData();
        }

        return $data;
    }
}
