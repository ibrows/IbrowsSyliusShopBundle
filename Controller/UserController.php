<?php

namespace Ibrows\SyliusShopBundle\Controller;

use Ibrows\SyliusShopBundle\Model\Address\AddressInterface;
use Ibrows\SyliusShopBundle\Model\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/register", name="user_register")
     * @Template("")
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function registerAction(Request $request)
    {
        $addressclass = $this->getInvoiceAddressClass();
        /** @var AddressInterface $address */
        $address = new $addressclass();

        $user = $this->getFOSUserManager()->createUser();
        /* @var $user UserInterface */
        $user->setEnabled(true);

        //can give a default email for form (will be overwritten in binding)
        $mail = $request->get('email');
        $user->setEmail($mail);

        $builder = $this->container->get('form.factory')->createNamedBuilder('registerform', 'form', null, array());

        $userform = $this->container->get('fos_user.registration.form.factory')->createForm();
        /* @var $userform \Symfony\Component\Form\Form */
        $userform->setData($user);
        $userform->remove('username');

        $builder->add('address', $this->getInvoiceAddressType(), array(
            'data_class' => $addressclass,
        ));
        $builder->get('address')->remove('email');

        $registerForm = $builder->getForm();
        $registerForm->setData(array('address' => $address));

        $registerForm->handleRequest($request);
        $userform->handleRequest($request);

        if ($registerForm->isSubmitted() && $userform->isSubmitted()) {
            if ($registerForm->isValid() && $userform->isValid()) {
                $address->setEmail($user->getEmail());
                $user->setInvoiceAddress($address);
                $this->getManagerForClass($address)->persist($address);
                $user->addRole($this->getParameter('ibrows_sylius_shop.user.role'));
                $this->getFOSUserManager()->updateUser($user);
                $this->getLoginManager()->logInUser($this->getParameter('fos_user.firewall_name'), $user);
                $this->getManagerForClass($address)->flush();
            }
        }

        if ($this->getUser() != null) {
            return $this->redirect($this->generateUrl('wizard_address'));
        }

        return array(
            'userform' => $userform->createView(),
            'registerform' => $registerForm->createView(),
            'user' => $this->getUser(),
        );
    }

    /**
     * @Route("/", name="user_profile")
     * @Template
     */
    public function profileAction()
    {
        return array(
            'user' => $this->getUser(),
        );
    }
}
