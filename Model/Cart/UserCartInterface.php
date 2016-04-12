<?php

namespace Ibrows\SyliusShopBundle\Model\Cart;

use FOS\UserBundle\Model\UserInterface;

interface UserCartInterface extends CartInterface
{
    /**
     * @param UserInterface $user
     *
     * @return UserCartInterface
     */
    public function setUser(UserInterface $user);

    /**
     * @return UserInterface
     */
    public function getUser();
}
