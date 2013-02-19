<?php

namespace Ibrows\SyliusShopBundle\Controller;

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

    public function initConfig()
    {
        $this->configuration = new Configuration($this->bundlePrefix, $this->resourceName);
    }

    /**
     * @param array $criteria
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function findOr404(array $criteria = null)
    {
        $config = $this->getConfiguration();

        if (null === $criteria) {
            $criteria = $config->getIdentifierCriteria();
        }

        if (!$resource = $this->getRepository()->findOneBy($criteria)) {
            throw new NotFoundHttpException(sprintf('Requested %s does not exist', $config->getResourceName()));
        }

        return $resource;
    }

    /**
     * Get configuration with the bound request.
     *
     * @return Configuration
     */
    public function getConfiguration()
    {
        if ($this->configuration == null) {
            $this->initConfig();
        }
        $this->configuration->setRequest($this->getRequest());
        return $this->configuration;
    }

    public function getRepository()
    {
        return $this->getService('repository');
    }

    public function getManager()
    {
        return $this->getService('manager');
    }

    protected function getService($name)
    {
        return $this->get($this->getConfiguration()->getServiceName($name));
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
        return $this->getCartProvider()->getCart();
    }

    /**
     * Get cart provider.
     *
     * @return CartProviderInterface
     */
    protected function getCartProvider()
    {
        return $this->get('sylius.cart_provider');
    }

    /**
     * Get cart item resolver.
     * This service is used to build the new cart item instance.
     *
     * @return CartResolverInterface
     */
    protected function getCartResolver()
    {
        return $this->get('sylius.cart_resolver');
    }

    protected function getProductRepository()
    {
        return $this->get('sylius.repository.product');
    }
}
