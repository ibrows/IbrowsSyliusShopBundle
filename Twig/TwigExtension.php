<?php

namespace Ibrows\SyliusShopBundle\Twig;

use Ibrows\SyliusShopBundle\Cart\CurrentCartManager;

class TwigExtension extends \Twig_Extension
{
    /**
     * @var CurrentCartManager
     */
    protected $currentCartManager;

    /**
     * @param CurrentCartManager $currentCartManager
     */
    public function __construct(CurrentCartManager $currentCartManager)
    {
        $this->currentCartManager = $currentCartManager;
    }

    /**
     * @return array
     */
    public function getFunctions(){
        return array(
            'getCurrentCartManager' => new \Twig_Function_Method($this, 'getCurrentCartManager'),
            'getCurrentCart' => new \Twig_Function_Method($this, 'getCurrentCart')
        );
    }
    /**
     * @return CurrentCartManager
     */
    public function getCurrentCartManager()
    {
        return $this->currentCartManager;
    }

    /**
     * @return CartInterface
     */
    public function getCurrentCart()
    {
       return $this->currentCartManager->getCart();
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ibrows_sylius_shop_bundle_extension';
    }
}