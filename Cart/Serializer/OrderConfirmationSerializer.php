<?php

namespace Ibrows\SyliusShopBundle\Cart\Serializer;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Serializer\CartSerializerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class OrderConfirmationSerializer implements CartSerializerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $from;

    /**
     * @var array|string
     */
    protected $to;

    /**
     * @var string
     */
    protected $templatePlain;

    /**
     * @var string
     */
    protected $templateHtml;

    /**
     * @var array|string
     */
    protected $bcc;

    /**
     * @var string
     */
    protected $translationDomain;

    /**
     * @param ContainerInterface $container
     * @param string $subject
     * @param string $from
     * @param string $templatePlain
     * @param string $templateHtml
     * @param string|array $bcc
     * @param string $translationDomain
     * @internal param string $template
     */
    public function __construct(ContainerInterface $container, $subject = null, $from = null, $templatePlain = null, $templateHtml = null, $bcc = null, $translationDomain = null)
    {
        $this->container = $container;
        $this->subject = $subject;
        $this->from = $from;
        $this->templatePlain = $templatePlain;
        $this->templateHtml = $templateHtml;
        $this->bcc = $bcc;
        $this->translationDomain = $translationDomain;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param ContainerInterface $container
     * @return OrderConfirmationSerializer
     */
    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @param CartInterface $cart
     * @return bool
     */
    public function accept(CartInterface $cart)
    {
        return true;
    }

    /**
     * @param CartInterface $cart
     * @return void
     */
    public function serialize(CartInterface $cart)
    {
        $this->getMailer()->send($this->getMessage($cart));
    }

    /**
     * @return \Swift_Mailer
     */
    protected function getMailer()
    {
        return $this->getContainer()->get('mailer');
    }

    /**
     * @return string
     */
    public function getTemplatePlain()
    {
        return $this->templatePlain;
    }

    /**
     * @param string $templatePlain
     * @return OrderConfirmationSerializer
     */
    public function setTemplatePlain($templatePlain)
    {
        $this->templatePlain = $templatePlain;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateHtml()
    {
        return $this->templateHtml;
    }

    /**
     * @param string $templateHtml
     * @return OrderConfirmationSerializer
     */
    public function setTemplateHtml($templateHtml)
    {
        $this->templateHtml = $templateHtml;
        return $this;
    }

    /**
     * @return TranslatorInterface
     */
    protected function getTranslator()
    {
        return $this->getContainer()->get('translator');
    }

    /**
     * @param CartInterface $cart
     * @return \Swift_Message
     */
    protected function getMessage(CartInterface $cart)
    {
        return \Swift_Message::newInstance()
            ->setSubject($this->getSubject($cart))
            ->setFrom($this->getFrom($cart))
            ->setTo($this->getTo($cart))
            ->setBcc($this->getBcc($cart))
            ->setBody($this->getHtmlBody($cart), 'text/html', 'utf-8')
            ->addPart($this->getPlainBody($cart))
            ;
    }

    /**
     * @param CartInterface $cart
     * @return string
     */
    protected function getPlainBody(CartInterface $cart)
    {
        return $this->getTemplating()->render($this->getTemplatePlain(), $this->getTemplateVariables($cart));
    }

    /**
     * @param CartInterface $cart
     * @return string
     */
    protected function getHtmlBody(CartInterface $cart)
    {
        return $this->getTemplating()->render($this->getTemplateHtml(), $this->getTemplateVariables($cart));
    }

    /**
     * @param CartInterface $cart
     * @return array
     */
    protected function getTemplateVariables(CartInterface $cart)
    {
        $cartManager = $this->container->get('ibrows_syliusshop.currentcart.manager');
        return array(
            'subject' => $this->getSubject($cart),
            'cart' => $cart,
            'translation_domain' => $this->getTranslationDomain(),
            'cartManager' => $cartManager,
            'deliveryOptionStrategy' => $cartManager->getSelectedDeliveryOptionStrategyService(),
            'paymentOptionStrategy' => $cartManager->getSelectedPaymentOptionStrategyService(),
            'deliveryOptionStrategyData' => $cart->getDeliveryOptionStrategyServiceData(),
            'paymentOptionStrategyData' => $cart->getPaymentOptionStrategyServiceData(),
        );
    }

    /**
     * @param CartInterface $cart
     * @return EngineInterface
     */
    protected function getTemplating(CartInterface $cart = null)
    {
        return $this->getContainer()->get('templating');
    }

    /**
     * @param CartInterface $cart
     * @return string|array
     */
    public function getBcc(CartInterface $cart = null)
    {
        return $this->bcc;
    }

    /**
     * @param array|string $bcc
     * @return OrderConfirmationSerializer
     */
    public function setBcc($bcc)
    {
        $this->bcc = $bcc;
        return $this;
    }

    /**
     * @param CartInterface $cart
     * @return string|array
     */
    public function getTo(CartInterface $cart = null)
    {
        $to = array();

        if(is_array($this->to)){
            $to = $this->to;
        }elseif(is_string($this->to)){
            $to = array($this->to);
        }

        $to[] = $cart->getEmail();

        return $to;
    }

    /**
     * @param array|string $to
     * @return OrderConfirmationSerializer
     */
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @param CartInterface $cart
     * @return string
     */
    public function getFrom(CartInterface $cart = null)
    {
        return $this->from;
    }

    /**
     * @param string $from
     * @return OrderConfirmationSerializer
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @param CartInterface $cart
     * @return string
     */
    public function getSubject(CartInterface $cart = null)
    {
        return $this->trans($this->subject, array('%id%' => $cart->getId()));
    }

    /**
     * @param string $subject
     * @return OrderConfirmationSerializer
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function getTranslationDomain()
    {
        return $this->translationDomain;
    }

    /**
     * @param string $translationDomain
     * @return OrderConfirmationSerializer
     */
    public function setTranslationDomain($translationDomain)
    {
        $this->translationDomain = $translationDomain;
        return $this;
    }

    /**
     * @param string $id
     * @param array $parameters
     * @param string $domain
     * @param string $locale
     * @return string
     */
    protected function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        return $this->getTranslator()->trans($id, $parameters, $domain?:$this->getTranslationDomain(), $locale);
    }
}