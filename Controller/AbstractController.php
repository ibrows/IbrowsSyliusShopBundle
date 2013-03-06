<?php

namespace Ibrows\SyliusShopBundle\Controller;

use Ibrows\SyliusShopBundle\Cart\CartManager;
use Ibrows\SyliusShopBundle\Cart\CurrentCartManager;

use Ibrows\SyliusShopBundle\Repository\ProductRepository;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

use Ibrows\SyliusShopBundle\IbrowsSyliusShopBundle;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Model\UserInterface;

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
            $criteria = array('id'=> $id);
        }

        if (!$resource = $repo->findOneBy($criteria)) {
            throw $this->createNotFoundException(sprintf(
                'Requested Entity "%s" with id "%s" does not exist',
                $repo->getClassName(),
                $id
            ));
        }

        return $resource;
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
    protected function getCurrentCartManager(){
        return $this->get('ibrows_syliusshop.currentcart.manager');
    }

    /**
     * @return CartManager
     */
    protected function getCartManager(){
        return $this->get('ibrows_syliusshop.cart.manager');
    }

    /**
     * @return CurrentCartManager
     */
    protected function persistCurrentCart()
    {
        return $this->getCurrentCartManager()->persistCart();
    }

    /**
     * @return ProductRepository
     */
    protected function getProductRepository()
    {
        $registry = $this->get('doctrine');
        $classname = $this->container->getParameter('ibrows_sylius_shop.product.class');
        $manager = $registry->getManagerForClass( $classname );
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
