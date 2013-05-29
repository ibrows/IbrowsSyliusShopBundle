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
            'getCurrentCartCurrency' => new \Twig_Function_Method($this, 'getCurrentCartCurrency'),
            'ibr_render_hinclude' => new \Twig_Function_Method($this, 'renderHinclude', array(
                'needs_environment' => true,
                'is_safe' => array('html')
            )),
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
     * @param \Twig_Environment $environment
     * @param string $uri
     * @param array $options
     * @return string
     */
    public function renderHinclude(\Twig_Environment $environment, $uri, array $options = array())
    {
        // We need to replace ampersands in the URI with the encoded form in order to return valid html/xml content.
        $uri = str_replace('&', '&amp;', $uri);

        $template = isset($options['default']) ? $options['default'] : $this->defaultHincludeTemplate;
        if ($template) {
            $content = $environment->render($template);
        } else {
            $content = $template;
        }

        $attributes = isset($options['attributes']) && is_array($options['attributes']) ? $options['attributes'] : array();
        if (isset($options['id']) && $options['id']) {
            $attributes['id'] = $options['id'];
        }

        $renderedAttributes = '';
        if (count($attributes) > 0) {
            foreach($attributes as $attribute => $value) {
                $renderedAttributes .= sprintf(
                    ' %s="%s"',
                    htmlspecialchars($attribute, ENT_QUOTES | ENT_SUBSTITUTE, $this->charset, false),
                    htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, $this->charset, false)
                );
            }
        }

        return sprintf('<hx:include src="%s"%s>%s</hx:include>', $uri, $renderedAttributes, $content);
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ibrows_sylius_shop_bundle_extension';
    }
}