<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Payment;

use Symfony\Component\HttpFoundation\Request;

class Context
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $currentRouteName;

    /**
     * @var string
     */
    protected $errorRouteName;

    /**
     * @param Request $request
     * @param string $currentRouteName
     * @param string $errorRouteName
     */
    public function __construct(Request $request, $currentRouteName, $errorRouteName)
    {
        $this->setRequest($request);
        $this->setCurrentRouteName($currentRouteName);
        $this->setErrorRouteName($errorRouteName);
    }

    /**
     * @return string
     */
    public function getCurrentRouteName()
    {
        return $this->currentRouteName;
    }

    /**
     * @param string $currentRouteName
     * @return Context
     */
    public function setCurrentRouteName($currentRouteName)
    {
        $this->currentRouteName = $currentRouteName;
        return $this;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return Context
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return string
     */
    public function getErrorRouteName()
    {
        return $this->errorRouteName;
    }

    /**
     * @param string $errorRouteName
     * @return Context
     */
    public function setErrorRouteName($errorRouteName)
    {
        $this->errorRouteName = $errorRouteName;
        return $this;
    }
}