<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response;

class SelfRedirectResponse
{
    /**
     * @var array
     */
    protected $parameters;

    public function __construct(array $parameters = array())
    {
        $this->setParameters($parameters);
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     * @return SelfRedirectResponse
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }
}