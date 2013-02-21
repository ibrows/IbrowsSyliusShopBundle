<?php

namespace Ibrows\SyliusShopBundle\Cart;

use Sylius\Bundle\CartBundle\Model\CartInterface as BaseCartInterface;

interface CartInterface extends BaseCartInterface
{
    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param string $email
     * @return CartInterface
     */
    public function setEmail($email);
}