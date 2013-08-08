<?php

namespace Ibrows\SyliusShopBundle\Twig;

use Ibrows\SyliusShopBundle\Cart\CurrentCartManager;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

class TwigExtension extends \Twig_Extension
{
    /**
     * @var CurrentCartManager
     */
    protected $currentCartManager;

    /**
     * @var string
     */
    protected $defaultHincludeTemplate;

    /**
     * @var string
     */
    protected $charset;

    /**
     * @param CurrentCartManager $currentCartManager
     * @param string $defaultHincludeTemplate
     * @param string $charset
     */
    public function __construct(
        CurrentCartManager $currentCartManager,
        $defaultHincludeTemplate,
        $charset
    ){
        $this->currentCartManager = $currentCartManager;
        $this->defaultHincludeTemplate = $defaultHincludeTemplate;
        $this->charset = $charset;
    }

    /**
     * @return array
     */
    public function getFunctions(){
        return array(
            'getCurrentCartManager' => new \Twig_Function_Method($this, 'getCurrentCartManager'),
            'getCurrentCart' => new \Twig_Function_Method($this, 'getCurrentCart'),
            'getCurrentCartCurrency' => new \Twig_Function_Method($this, 'getCurrentCartCurrency')
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
    public function getCurrentCartCurrency()
    {
        return $this->currentCartManager->getCart()->getCurrency();
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ibrows_sylius_shop_bundle_extension';
    }
}