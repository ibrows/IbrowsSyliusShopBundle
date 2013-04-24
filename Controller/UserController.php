<?php

namespace Ibrows\SyliusShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{

    /**
     * @Route("/register", name="user_register")
     * @Template("")
     */
    public function registerAction(){
        $addressclass = $this->getInvoiceAddressClass();
        $address = new $addressclass();
        $user = $this->getFOSUserManager()->createUser();
        $user->setEnabled(true);;

        $builder = $this->container->get('form.factory')->createNamedBuilder('registerform', 'form', null, array());

        $userform = $this->container->get('fos_user.registration.form.factory')->createForm();
        /* @var $userform \Symfony\Component\Form\Form   */
        $userform->setData($user);
        $userform->remove('username');

        $builder->add('address',$this->getInvoiceAddressType(),array('data_class' =>$addressclass));

        $registerForm = $builder->getForm();

        $registerForm->setData(array('address'=>$address));
        if($this->getRequest()->getMethod() == 'POST'){
            $registerForm->bind($this->getRequest());
            $userform->bind($this->getRequest());
            if($registerForm->isValid() && $userform->isValid()){
                $user->addRole('ibrows_sylius_shop.user.role');
                $this->getFOSUserManager()->updateUser($user);
                $this->getLoginManager()->loginUser(
                        $this->getParameter('fos_user.firewall_name'),
                        $user
                );
            }
        }

        return array(
                'userform' => $userform->createView(),
                'registerform' => $registerForm->createView(),
                'user' => $this->getUser()
        );
    }

    /**
     * @Route("/", name="user_profile")
     * @Template
     */
    public function profileAction()
    {
        return array(
            'user' => $this->getUser()
        );
    }
}