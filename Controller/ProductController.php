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
 *
 */
class ProductController extends ResourceController
{

    /**
     * Get collection (paginated by default) of resources.
     */
    public function indexAction(Request $request)
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

        $view = $this
        ->view()
        ->setTemplate('IbrowsSyliusShopBundle:Product:list.html.twig')
        ->setTemplateVar($pluralName)
        ->setData($resources)
        ;

        return $this->handleView($view);
    }

    public function showAction()
    {
        $view =  $this
        ->view()
        ->setTemplate('IbrowsSyliusShopBundle:Product:show.html.twig')
        ->setTemplateVar($this->getConfiguration()->getResourceName())
        ->setData($this->findOr404())
        ;

        return $this->handleView($view);
    }
}
