<?php

namespace Ibrows\SyliusShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class AbstractController extends Controller
{

    protected function getProductRepository()
    {
        return $this->get('sylius.repository.product');
    }
}