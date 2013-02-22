<?php

namespace Ibrows\SyliusShopBundle\Model\Cart;

use Sylius\Bundle\CartBundle\Model\CartItemInterface as BaseCartItemInterface;

use DateTime;

interface CartItemInterface extends BaseCartItemInterface
{
    /**
     * @return bool
     */
    public function isDelivered();

    /**
     * @param bool $flag
     * @return CartItemInterface
     */
    public function setDelivered($flag = true);

    /**
     * @return DateTime
     */
    public function getDeliveredAt();
}