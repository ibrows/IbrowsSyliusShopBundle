<?php

namespace Ibrows\SyliusShopBundle\Controller;

use Doctrine\Common\Persistence\ObjectRepository;

use Ibrows\SyliusShopBundle\Cart\BaseManager;
use Ibrows\SyliusShopBundle\Repository\ProductRepository;

use Sylius\Bundle\ResourceBundle\Controller\Configuration;
use Sylius\Bundle\CartBundle\Model\CartInterface;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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
     * @return ProductRepository
     */
    protected function getProductRepository()
    {
        return $this->get('sylius.repository.product');
    }
}
