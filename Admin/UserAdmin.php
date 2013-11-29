<?php

namespace Ibrows\SyliusShopBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class UserAdmin extends DefaultAdmin
{
	public function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		->addIdentifier('id')
		->add('email')
		->add('lastLogin')
		->add('enabled')
		->add('roles')
		;
	}


    public function configureDatagridFilters(DatagridMapper $datagrid) {

        $datagrid->add('id');
        $datagrid->add('email', 'doctrine_orm_string', array());

        $datagrid->add('myroles', 'doctrine_orm_callback', array(
            'callback' => function($queryBuilder, $alias, $field, $options) {
                /* @var $queryBuilder Sonata\AdminBundle\Datagrid\ORM\ProxyQuery */
                $value = $options['value'];
                if (!$value) {
                    return;
                }
                $queryBuilder->add('where', $queryBuilder->expr()->orx(
                                $queryBuilder->expr()->like($alias . '.roles', '?1')
                        ));
                $queryBuilder->setParameters(array(1 => "%:\"$value\";%"));
            },
            'field_type' => 'choice',
                ), 'choice', array(
            'choices' => $this->getRoles(),
            'expanded' => false,
            'required' => false,
            'multiple' => false,
                )
        );

    }


    public function getRoles() {
        $roles = array();

        foreach ($this->configurationPool->getAdminServiceIds() as $id) {
            try {
                $admin = $this->configurationPool->getInstance($id);
            } catch (\Exception $e) {
                continue;
            }

            $securityHandler = $admin->getSecurityHandler();

            foreach ($securityHandler->buildSecurityInformation($admin) as $role => $acls) {
                $roles[$role] = $role;
            }
        }

        // get roles from the service container
        foreach ($this->configurationPool->getContainer()->getParameter('security.role_hierarchy.roles') as $name => $rolesHierarchy) {
            $roles[$name] = $name; //  . ': ' . implode(', ', $rolesHierarchy)

            foreach ($rolesHierarchy as $role) {
                if (!isset($roles[$role])) {
                    //$roles[$role] = $role;
                }
            }
        }
        return $roles;
    }



    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->with('General')
                ->add('email', 'email', array('required' => true))
                ->add('plainPassword', 'password', array('required' => false))
                //->add('plainPassword', 'repeated', array('first_name' => $this->trans('Password', array(), $this->translationDomain),'second_name' => $this->trans('Password', array(), $this->translationDomain),'required' => (!$this->getSubject()->getId() > 0), 'type' => 'password', 'error_bubbling' => false, 'invalid_message' => 'Password don\'t match each other'))
                ->add('roles', 'choice', array('choices'=>$this->getRoles(), 'multiple' => true, 'required' => false, 'expanded' => true))
                ->add('locked', null, array('required' => false))
                ->add('expired', null, array('required' => false))
                ->add('enabled', null, array('required' => false))
                ->add('credentialsExpired', null, array('required' => false))
        ;
    }

    public function preUpdate($user) {
        parent::preUpdate($user);
        $this->updateFOSUser($user);
    }

    private function updateFOSUser($entity) {
        /* @var $userManager \FOS\UserBundle\Entity\UserManager */
        $userManager = $this->getConfigurationPool()->getContainer()->get('fos_user.user_manager');
        //$userarr = $this->getRequest()->request->get('user');
        // $entity->setPlainPassword($userarr['plainpassword']);
        $userManager->updateCanonicalFields($entity);
        $userManager->updatePassword($entity);
        return $entity;
    }

    public function getUniqid() {
        return 'usertype';
    }

}