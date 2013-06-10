<?php

namespace Ibrows\SyliusShopBundle\Controller;

use Ibrows\SyliusShopBundle\Cart\Exception\CartItemNotOnStockException;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\CurrentCartManager;
use Ibrows\SyliusShopBundle\Form\DeliveryOptionStrategyType;
use Ibrows\SyliusShopBundle\Form\InvoiceSameAsDeliveryType;
use Ibrows\SyliusShopBundle\Form\PaymentOptionStrategyType;
use Ibrows\SyliusShopBundle\Form\SummaryType;
use Ibrows\SyliusShopBundle\Repository\ProductRepository;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Login\LoginInformationInterface;
use Ibrows\SyliusShopBundle\Form\AuthType;
use Ibrows\SyliusShopBundle\Form\LoginType;
use Ibrows\SyliusShopBundle\Form\BasketType;
use Ibrows\SyliusShopBundle\Form\BasketItemType;
use Ibrows\SyliusShopBundle\Form\DeliveryAddressType;
use Ibrows\SyliusShopBundle\Form\InvoiceAddressType;
use Ibrows\SyliusShopBundle\Model\Address\InvoiceAddressInterface;
use Ibrows\SyliusShopBundle\Model\Address\DeliveryAddressInterface;
use Ibrows\SyliusShopBundle\IbrowsSyliusShopBundle;
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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Ibrows\SyliusShopBundle\Entity\Address;

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
            throw $this->createNotFoundException(sprintf('Requested Entity "%s" with id "%s" does not exist', $repo->getClassName(), $id));
        }

        return $resource;
    }

    /**
     * @param string $className
     * @return ObjectRepository
     */
    protected function getRepository($className)
    {
        return $this->getManagerForClass($className)->getRepository($className);
    }

    /**
     * @param string $className
     * @return ObjectManager
     */
    protected function getManagerForClass($className)
    {
        return $this->getDoctrine()->getManagerForClass($className);
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
     * @param bool $refreshAndCheckAvailability
     */
    protected function persistCurrentCart($refreshAndCheckAvailability = true)
    {
        $this->persistCart(
            $this->getCurrentCartManager(),
            $refreshAndCheckAvailability
        );
    }

    /**
     * @param CartManager $cartManager
     * @param bool $refreshAndCheckAvailability
     */
    protected function persistCart(CartManager $cartManager, $refreshAndCheckAvailability = true)
    {
        try {
            $cartManager->persistCart($refreshAndCheckAvailability);
        } catch (CartItemNotOnStockException $e) {
            foreach ($e->getCartItemsNotOnStock() as $itemNotOnStock) {
                $item = $itemNotOnStock->getItem();
                if(!$item->getProduct()->isEnabled()){
                    $message = $item . ' not found';
                    $this->get('session')->getFlashBag()->add('notice', $message);
                    $cartManager->removeItem($item);
                    continue;
                }
                $message = $item . ' ' . $item->getQuantityNotAvailable() . " not there...";
                $this->get('session')->getFlashBag()->add('notice', $message);
                $item->setQuantityToAvailable();
            }
            $cartManager->persistCart($refreshAndCheckAvailability);
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
     */
    protected function authDelete(CartManager $cartManager)
    {
        $cartManager->getCart()->setEmail(null);
        $this->persistCart($cartManager);
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
                $this->persistCart($cartManager);
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
     * @return InvoiceSameAsDeliveryType
     */
    protected function getInvoiceSameAsDeliveryType()
    {
        return new InvoiceSameAsDeliveryType();
    }

    /**
     * @return LoginType
     */
    protected function getLoginType()
    {
        return new LoginType();
    }

    /**
     * @return SummaryType
     */
    protected function getSummaryType()
    {
        return new SummaryType();
    }

    /**
     * @param CartManager $cartManager
     * @return DeliveryOptionStrategyType
     */
    protected function getDeliveryOptionStrategyType(CartManager $cartManager)
    {
        return new DeliveryOptionStrategyType($cartManager);
    }

    /**
     * @param CartManager $cartManager
     * @return PaymentOptionStrategyType
     */
    protected function getPaymentOptionStrategyType(CartManager $cartManager)
    {
        return new PaymentOptionStrategyType($cartManager);
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
     * @return DeliveryAddressInterface
     */
    protected function getNewDeliveryAddress()
    {
        $className = $this->getDeliveryAddressClass();
        return new $className();
    }

    /**
     * @return InvoiceAddressType
     */
    protected function getInvoiceAddressType()
    {
        return new InvoiceAddressType(
            $this->getInvoiceAddressTypeCountryChoices(),
            $this->getInvoiceAddressTypePreferredCountryChoices(),
            $this->getInvoiceAddressTypeTitleChoices());
    }

    /**
     * @return DeliveryAddressType
     */
    protected function getDeliveryAddressType()
    {
        return new DeliveryAddressType(
            $this->getDeliveryAddressTypeCountryChoices(),
            $this->getDeliveryAddressTypePreferredCountryChoices(),
            $this->getDeliveryAddressTypeTitleChoices()
        );
    }

    /**
     * @return array
     */
    protected function getInvoiceAddressTypeTitleChoices()
    {
        return $this->getAddressTypeTitleChoices();
    }

    /**
     * @return array
     */
    protected function getInvoiceAddressTypePreferredCountryChoices()
    {
        return $this->getAddressTypePreferredCountryChoices();
    }

    /**
     * @return array
     */
    protected function getInvoiceAddressTypeCountryChoices()
    {
        return $this->getAddressTypeCountryChoices();
    }

    /**
     * @return array
     */
    protected function getDeliveryAddressTypeTitleChoices()
    {
        return $this->getAddressTypeTitleChoices();
    }

    /**
     * @return array
     */
    protected function getDeliveryAddressTypePreferredCountryChoices()
    {
        return $this->getAddressTypePreferredCountryChoices();
    }

    /**
     * @return array
     */
    protected function getDeliveryAddressTypeCountryChoices()
    {
        return $this->getAddressTypeCountryChoices();
    }

    /**
     * @return array
     */
    protected function getAddressTypeCountryChoices()
    {
        return array();
    }

    /**
     * @return array
     */
    protected function getAddressTypePreferredCountryChoices()
    {
        return array();
    }

    /**
     * @return array
     */
    protected function getAddressTypeTitleChoices()
    {
        return array_flip(Address::getTitles());
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

    /**
     * @return string
     */
    public function getPaymentOptionsClass()
    {
        return $this->container->getParameter('ibrows_sylius_shop.paymentoptions.class');
    }
}
