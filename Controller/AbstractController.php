<?php

namespace Ibrows\SyliusShopBundle\Controller;

use Doctrine\Common\Persistence\ObjectRepository;

use Ibrows\SyliusShopBundle\Cart\BaseManager;

use Sylius\Bundle\ResourceBundle\Controller\Configuration;
use Sylius\Bundle\CartBundle\Model\CartInterface;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractController extends Controller
{

    /**
     * @var Configuration
     */
    protected $configuration;
    protected $bundlePrefix = 'sylius';
    protected $resourceName = null;



    /**
     * @param array $criteria
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function findOr404(ObjectRepository $repo, array $criteria = null)
    {

        if (null === $criteria) {
            $criteria = array('id'=> $this->getRequest()->get('id'));
        }

        if (!$resource = $repo->findOneBy($criteria)) {
            throw new NotFoundHttpException(sprintf('Requested %s does not exist', $config->getResourceName()));
        }

        return $resource;
    }


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

    protected function getProductRepository()
    {
        return $this->get('sylius.repository.product');
    }
}
