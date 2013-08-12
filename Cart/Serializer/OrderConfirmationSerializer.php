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
    protected $template;

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
     * @param string $template
     * @param string|array $bcc
     * @param string $translationDomain
     */
    public function __construct(ContainerInterface $container, $subject = null, $from = null, $template = null, $bcc = null, $translationDomain = null)
    {
        $this->container = $container;
        $this->subject = $subject;
        $this->from = $from;
        $this->template = $template;
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
            ->setBody($this->getBody($cart))
        ;
    }

    /**
     * @param CartInterface $cart
     * @return string
     */
    protected function getBody(CartInterface $cart)
    {
        return $this->getTemplating()->render($this->getTemplate(), array('cart' => $cart));
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
     * @return string
     */
    public function getTemplate(CartInterface $cart = null)
    {
        return $this->template;
    }

    /**
     * @param string $template
     * @return OrderConfirmationSerializer
     */
    public function setTemplate($template)
    {
        $this->template = $template;
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

        return $this->to;
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