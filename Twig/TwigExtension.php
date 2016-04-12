<?php

namespace Ibrows\SyliusShopBundle\Twig;

use Ibrows\SyliusShopBundle\Cart\CurrentCartManager;
use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

class TwigExtension extends \Twig_Extension
{
    /**
     * @var CurrentCartManager
     */
    private $currentCartManager;

    /**
     * @var string
     */
    private $defaultHincludeTemplate;

    /**
     * @var string
     */
    private $charset;

    /**
     * @param CurrentCartManager $currentCartManager
     * @param string $defaultHincludeTemplate
     * @param string $charset
     */
    public function __construct(
        CurrentCartManager $currentCartManager,
        $defaultHincludeTemplate,
        $charset
    )
    {
        $this->currentCartManager = $currentCartManager;
        $this->defaultHincludeTemplate = $defaultHincludeTemplate;
        $this->charset = $charset;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'getCurrentCartManager'  => new \Twig_SimpleFunction('getCurrentCartManager', array($this, 'getCurrentCartManager')),
            'getCurrentCart'         => new \Twig_SimpleFunction('getCurrentCart', array($this, 'getCurrentCart')),
            'getCurrentCartCurrency' => new \Twig_SimpleFunction('getCurrentCartCurrency', array($this, 'getCurrentCartCurrency')),
        );
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            'price' => new \Twig_SimpleFilter('price', array($this, 'price'), array(
                'is_safe'           => array('html'),
                'needs_environment' => true,
            )),
        );
    }

    /**
     * @param \Twig_Environment $twig
     * @param $num
     * @return string
     */
    public function price(\Twig_Environment $twig, $num)
    {
        return $twig->render('IbrowsSyliusShopBundle:Misc:price.html.twig', array(
            'price' => $num,
        ));
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
    public function getName()
    {
        return 'ibrows_sylius_shop_bundle_extension';
    }
}
