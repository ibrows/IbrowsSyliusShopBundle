<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response;

abstract class AbstractRedirectResponse
{
    /**
     * @var array
     */
    protected $parameters;

    /**
     * @param array $parameters
     */
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
     *
     * @return SelfRedirectResponse
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }
}
