<?php

namespace Ibrows\SyliusShopBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Ibrows\SyliusShopBundle\Model\User\UserInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{

    /**
     * @Route("/register", name="user_register")
     * @Template("")
     */
    public function registerAction()
    {
        $addressclass = $this->getInvoiceAddressClass();
        $address = new $addressclass();
        $user = $this->getFOSUserManager()->createUser();
        /* @var $user UserInterface   */
        $user->setEnabled(true);

        //can give a default email for form (will be overwritten in binding)
        $mail = $this->getRequest()->get('email');
        $user->setEmail($mail);

        $builder = $this->container->get('form.factory')->createNamedBuilder('registerform', 'form', null, array());

        $userform = $this->container->get('fos_user.registration.form.factory')->createForm();
        /* @var $userform \Symfony\Component\Form\Form   */
        $userform->setData($user);
        $userform->remove('username');

        $builder->add('address', $this->getInvoiceAddressType(), array(
                        'data_class' => $addressclass
                ));
        $builder->get('address')->remove('email');
        $registerForm = $builder->getForm();

        $registerForm->setData(array('address' => $address));
        if ($this->getRequest()->getMethod() == 'POST') {
            $registerForm->bind($this->getRequest());
            $userform->bind($this->getRequest());
            if ($registerForm->isValid() && $userform->isValid()) {
                $address->setEmail($user->getEmail());
                $user->setInvoiceAddress($address);
                $this->getManagerForClass($address)->persist($address);
                $user->addRole($this->getParameter('ibrows_sylius_shop.user.role'));
                $this->getFOSUserManager()->updateUser($user);
                $this->getLoginManager()->loginUser($this->getParameter('fos_user.firewall_name'), $user);
                $this->getManagerForClass($address)->flush();
            }
        }

        if($this->getUser() != null){
            return $this->redirect($this->generateUrl('wizard_address'));
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
