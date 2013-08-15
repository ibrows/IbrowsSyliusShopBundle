<?php

namespace Ibrows\SyliusShopBundle\Model\Cart\Strategy;

interface CartDefaultOptionStrategyInterface extends CartStrategyInterface
{
    /**
     * @param bool $flag
     * @return CartDefaultOptionStrategyInterface
     */
    public function setDefault($flag = true);

    /**
     * @return bool
     */
    public function isDefault();
}