<?php

namespace Ibrows\SyliusShopBundle\Login;

use FOS\UserBundle\Model\UserInterface;

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

    /**
     * @return UserInterface
     */
    public function getUser();
}
