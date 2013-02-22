<?php

namespace Ibrows\SyliusShopBundle\Controller;

use Doctrine\Common\Persistence\ObjectRepository;

use Ibrows\SyliusShopBundle\Cart\BaseManager;
use Ibrows\SyliusShopBundle\Repository\ProductRepository;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

use Ibrows\SyliusShopBundle\IbrowsSyliusShopBundle;

use Sylius\Bundle\ResourceBundle\Controller\Configuration;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Translation\TranslatorInterface;

use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Model\UserInterface;

abstract class AbstractController extends Controller
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var string
     */
    protected $bundlePrefix = 'sylius';

    /**
     * @var string
     */
    protected $resourceName = null;

    /**
     * @param ObjectRepository $repo
     * @param array $criteria
     * @return object
     */
    public function findOr404(ObjectRepository $repo, array $criteria = null)
    {
        $id = $this->getRequest()->get('id');

        if (null === $criteria) {
            $criteria = array('id'=> $id);
        }

        if (!$resource = $repo->findOneBy($criteria)) {
            $this->createNotFoundException(sprintf(
                'Requested Entity "%s" with id "%s" does not exist',
                $repo->getClassName(),
                $id
            ));
        }

        return $resource;
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
     * Get current cart using the provider service.
     *
     * @return CartInterface
     */
    protected function getCurrentCart()
    {
        return $this->getCartManager()->getCurrentCart();
    }

    /**
     * @return BaseManager
     */
    protected function getCartManager(){
        return $this->get('ibrows_syliusshop.cart.manager');
    }

    /**
     * @param CartInterface $cart
     * @return BaseManager
     */
    protected function persistCart(CartInterface $cart)
    {
        return $this->getCartManager()->persistCart($cart);
    }

    /**
     * @return ProductRepository
     */
    protected function getProductRepository()
    {
        return $this->get('sylius.repository.product');
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
        $id = $this->getTranslationPrefix().'.'.$id;
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
}
