<?php

namespace Ibrows\SyliusShopBundle\Controller;
use Ibrows\SyliusShopBundle\Cart\Exception\CartItemNotOnStockException;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\CurrentCartManager;

use Ibrows\SyliusShopBundle\Repository\ProductRepository;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

use Ibrows\SyliusShopBundle\Login\LoginInformationInterface;
use Ibrows\SyliusShopBundle\Form\AuthType;
use Ibrows\SyliusShopBundle\Form\LoginType;
use Ibrows\SyliusShopBundle\Form\BasketType;
use Ibrows\SyliusShopBundle\Form\BasketItemType;

use Ibrows\SyliusShopBundle\Form\DeliveryAddressType;
use Ibrows\SyliusShopBundle\Form\InvoiceAddressType;
use Ibrows\SyliusShopBundle\Entity\Address;

use Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface;

use Ibrows\SyliusShopBundle\IbrowsSyliusShopBundle;

use Ibrows\SyliusShopBundle\Cart\Exception\CartException;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Ibrows\Bundle\WizardAnnotationBundle\Annotation\AnnotationHandler as WizardHandler;
use Symfony\Component\Form\FormError;

use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Security\LoginManager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

abstract class AbstractController extends Controller
{
    /**
     * @param ObjectRepository $repo
     * @param array $criteria
     * @return object
     * @throws NotFoundHttpException
     */
    public function findOr404(ObjectRepository $repo, array $criteria = null)
    {
        $id = $this->getRequest()->get('id');

        if (null === $criteria) {
            $criteria = array(
                    'id' => $id
            );
        }

        if (!$resource = $repo->findOneBy($criteria)) {
            throw $this
                    ->createNotFoundException(sprintf('Requested Entity "%s" with id "%s" does not exist', $repo->getClassName(), $id));
        }

        return $resource;
    }

    /**
     * @param string $className
     * @return ObjectRepository
     */
    protected function getRepository($className)
    {
        return $this->getDoctrine()->getManagerForClass($className)->getRepository($className);
    }

    /**
     * @return LoginInformationInterface
     */
    protected function getLoginInformation()
    {
        return $this->get('ibrows_syliusshop.login.logininformation');
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function getParameter($name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * @param string $name
     * @return ObjectManager
     */
    protected function getObjectManager($name = null)
    {
        return $this->getDoctrine()->getManager($name);
    }

    /**
     * @return LoginManager
     */
    protected function getLoginManager()
    {
        return $this->get('fos_user.security.login_manager');
    }

    /**
     * @param string $name
     * @return Response
     */
    protected function forwardByRoute($name)
    {
        $defaults = $this->get('router')->getRouteCollection()->get($name)->getDefaults();
        return $this->forward($defaults['_controller'], array(), $this->container->get('request')->query->all());
    }

    /**
     * Cart summary page route.
     *
     * @return string
     */
    protected function getCartSummaryRoute()
    {
        return 'cart_summary';
    }

    /**
     * @return CurrentCartManager
     */
    protected function getCurrentCartManager()
    {
        return $this->get('ibrows_syliusshop.currentcart.manager');
    }

    /**
     * @return CartManager
     */
    protected function getCartManager()
    {
        return $this->get('ibrows_syliusshop.cart.manager');
    }

    /**
     * @return CartInterface
     */
    protected function getCurrentCart()
    {
        return $this->getCurrentCartManager()->getCart();
    }

    /**
     * @return CartManager
     * @throws CartException
     */
    protected function persistCurrentCart()
    {
        try {
            return $this->getCurrentCartManager()->persistCart();
        } catch (CartItemNotOnStockException $e) {
            foreach ($e->getCartItemsNotOnStock() as $itemNotOnStock) {
                $item = $itemNotOnStock->getItem();
                $message = $item . ' ' . $item->getQuantityNotAvailable() . " not there...";
                $this->get('session')->getFlashBag()->add('notice', $message);
                $item->setQuantityToAvailable();
            }
            return $this->getCurrentCartManager()->persistCart();
        }

    }

    /**
     * @return ProductRepository
     */
    protected function getProductRepository()
    {
        $registry = $this->get('doctrine');
        $classname = $this->container->getParameter('ibrows_sylius_shop.product.class');
        $manager = $registry->getManagerForClass($classname);
        return $manager->getRepository($classname);
    }

    /**
     * @return TranslatorInterface
     */
    protected function getTranslator()
    {
        return $this->get('translator');
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return parent::getUser();
    }

    /**
     * @param string $id
     * @param array $parameters
     * @param string $domain
     * @param string $locale
     * @return string
     */
    protected function translateWithPrefix($id, array $parameters = array(), $domain = null, $locale = null)
    {
        $id = $this->getTranslationPrefix() . '.' . $id;
        return $this->getTranslator()->trans($id, $parameters, $domain, $locale);
    }

    /**
     * @return UserManagerInterface
     */
    protected function getFOSUserManager()
    {
        return $this->get('fos_user.user_manager');
    }

    /**
     * @return string
     */
    protected function getTranslationPrefix()
    {
        return IbrowsSyliusShopBundle::TRANSLATION_PREFIX;
    }

    /**
     * @param CartManager $cartManager
     * @return CartManager
     */
    protected function authDelete(CartManager $cartManager)
    {
        $cartManager->getCart()->setEmail(null);
        return $cartManager->persistCart();
    }

    /**
     * @param Request $request
     * @param FormInterface $authForm
     * @param CartManager $cartManager
     * @param WizardHandler $wizard
     * @return RedirectResponse|void
     */
    protected function authByEmail(Request $request, FormInterface $authForm, CartManager $cartManager, WizardHandler $wizard)
    {
        $authForm->bind($request);
        if ($authForm->isValid()) {
            $email = $authForm->get('email')->getData();
            if ($this->getFOSUserManager()->findUserByEmail($email)) {
                $authForm->addError(new FormError($this->translateWithPrefix("user.emailallreadyexisting", array('%email%' => $email), "validators")));
            } else {
                $cartManager->getCart()->setEmail($email);
                $cartManager->persistCart();
                return $this->redirect($wizard->getNextStepUrl());
            }
        }
    }

    /**
     * @return AuthType
     */
    protected function getAuthType()
    {
        return new AuthType();
    }

    /**
     * @return LoginType
     */
    protected function getLoginType()
    {
        return new LoginType();
    }

    /**
     * @return BasketType
     */
    protected function getBasketType()
    {
        return new BasketType($this->getBasketItemType());
    }

    /**
     * @return BasketItemType
     */
    protected function getBasketItemType()
    {
        return new BasketItemType($this->getBasketItemDataClass());
    }

    /**
     * @return string
     */
    protected function getBasketItemDataClass()
    {
        return get_class($this->getCurrentCartManager()->createNewItem());
    }

    /**
     * @return InvoiceAddressInterface
     */
    protected function getNewInvoiceAddress()
    {
        $className = $this->getInvoiceAddressClass();
        return new $className();
    }

    /**
     * @return InvoiceAddressInterface
     */
    protected function getNewDeliveryAddress()
    {
        $className = $this->getDeliveryAddressClass();
        return new $className();
    }

    /**
     * @param CartInterface $cart
     * @return PaymentInstruction
     */
    protected function getNewPaymentInstruction(CartInterface $cart)
    {
        $className = $this->getParameter('ibrows_sylius_shop.paymentinstructions.class');
        return new $className($cart->getTotalItems(), 'CHF', null);
    }

    /**
     * @return InvoiceAddressType
     */
    protected function getInvoiceAddressType()
    {
        return new InvoiceAddressType();
    }

    /**
     * @return DeliveryAddressType
     */
    protected function getDeliveryAddressType()
    {
        return new DeliveryAddressType();
    }

    /**
     * @return string
     */
    public function getInvoiceAddressClass()
    {
        return $this->container->getParameter('ibrows_sylius_shop.invoiceaddress.class');
    }

    /**
     * @return string
     */
    public function getDeliveryAddressClass()
    {
        return $this->container->getParameter('ibrows_sylius_shop.deliveryaddress.class');
    }

}
