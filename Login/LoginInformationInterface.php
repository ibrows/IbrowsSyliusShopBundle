<?php

namespace Ibrows\SyliusShopBundle\Login;

interface LoginInformationInterface
{
    /**
     * @return string|null
     */
    public function getLastUsername();

    /**
     * @return string|null
     */
    public function getAuthenticationError();

    /**
     * @return string
     */
    public function getCsrfToken();
}