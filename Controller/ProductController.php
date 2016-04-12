<?php

namespace Ibrows\SyliusShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author marcsteiner
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="product_list")
     * @Template("")
     * Get collection (paginated by default) of resources.
     */
    public function listAction()
    {
        $resources = $this->getProductRepository()->findBy(array(), array(), 100);

        return array(
            'products' => $resources,
        );
    }

    /**
     * @Route("/show/{slug}", name="product_show")
     * @Template("")
     * @param Request $request
     * @param $slug
     * @return array
     */
    public function showAction(Request $request, $slug)
    {
        $data = $this->findOr404($request, $this->getProductRepository(), array('slug' => $slug));

        return array(
            'product' => $data,
        );
    }
}
