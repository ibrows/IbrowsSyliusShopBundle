<?php

namespace Ibrows\SyliusShopBundle\Controller;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;

use Ibrows\SyliusShopBundle\Controller\AbstractController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Sylius\Bundle\CartBundle\SyliusCartEvents;
use Sylius\Bundle\CartBundle\Event\CartItemEvent;
use Sylius\Bundle\CartBundle\Event\FlashEvent;
use Sylius\Bundle\CartBundle\Resolver\ItemResolvingException;

/**
 * @author marcsteiner
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    /**
     * @var string
     */
    protected $resourceName = 'product';

    /**
     * @Route("/", name="product_list")
     * @Template("")
     * Get collection (paginated by default) of resources.
     */
    public function listAction(Request $request)
    {
        $config = $this->getConfiguration();

        $criteria = $config->getCriteria();
        $sorting = $config->getSorting();

        $pluralName = $config->getPluralResourceName();

        if ($config->isPaginated()) {
            $resources = $this
            ->getRepository()
            ->createPaginator($criteria, $sorting)
            ->setCurrentPage($request->get('page', 1), true, true)
            ->setMaxPerPage($config->getPaginationMaxPerPage())
            ;
        } else {
            $resources = $this
            ->getRepository()
            ->findBy($criteria, $sorting, $config->getLimit())
            ;
        }

        return array($pluralName=>$resources);
    }
    /**
     * @Route("/show/{slug}", name="product_show", defaults={"_identifier"="slug"})
     * @Template("")
     */
    public function showAction()
    {
        $data = $this->findOr404();
        return array($this->getConfiguration()->getResourceName() =>$data);
    }
}
