<?php

namespace Ibrows\SyliusShopBundle\EasysysConnector;


use EasysysConnector\Manager\Resource\Kb\ResourcePositionCustomManager;
use Remdan\EasysysConnectorBundle\EasysysConnectorManager as BaseEasysysConnectorManager;
use EasysysConnector\HttpAdapter\HttpAdapterInterface;
use EasysysConnector\HttpAdapter\HttpParameterBag;
use EasysysConnector\HttpAdapter\HttpResponse;
use EasysysConnector\Model\Resource\Kb\ResourcePositionCustomInterface;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;

class EasysysConnectorManager extends BaseEasysysConnectorManager
{
    /**
     * @param CartInterface $cart
     * @return mixed
     */
    public function pushKbOrder(CartInterface $cart)
    {
        return $this->get($cart->getEsResource())->createData($cart);
    }

    /**
     * @param ResourcePositionCustomInterface $positionCustom
     * @return ResourcePositionCustomInterface
     */
    public function pushKbPositionCustom(ResourcePositionCustomInterface $positionCustom)
    {
        /** @var ResourcePositionCustomManager $manager */
        $manager = $this->get($positionCustom->getEsResource());
        $positionCustom = $manager->createData($positionCustom);

        return $positionCustom;
    }

    /**
     * @param int $invoiceId
     * @param float $invoiceAmount
     * @return HttpResponse
     * @throws \Exception
     */
    public function setInvoiceToPayed($invoiceId, $invoiceAmount)
    {
        $parameterBag = clone $this->getEasysysConnector()->getHttpParameterBag();

        $parameterBag->setMethod(HttpAdapterInterface::HTTP_METHOD_POST);
        $parameterBag->setParameterPostFormat('application/json');
        $parameterBag->setParameterPost(array(
            'value' => $invoiceAmount,
        ));

        $requestUri = (string)vsprintf('kb_invoice/%d/payment', array($invoiceId));
        $parameterBag->setUri($requestUri);

        $parameterBag->setHeaders($this->easysysConnector->getAuthAdapter()->getDefaultHeaders($parameterBag));

        return $this->easysysConnector->getManager()->execute($parameterBag);
    }

    /**
     * @param CartInterface $cart
     * @param $text
     * @return HttpResponse
     */
    public function pushKbComment(CartInterface $cart, $text)
    {
        $parameterBag = clone $this->getEasysysConnector()->getHttpParameterBag();

        $parameterBag->setMethod(HttpAdapterInterface::HTTP_METHOD_POST);
        $parameterBag->setParameterPostFormat('application/json');
        $parameterBag->setParameterPost(array(
            'user_id' => $cart->getEsUserId(),
            'text' => $text,
            'is_public' => false
        ));

        $requestUri = (string)vsprintf('kb_order/%d/comment', array($cart->getEsId()));
        $parameterBag->setUri($requestUri);

        $parameterBag->setHeaders($this->easysysConnector->getAuthAdapter()->getDefaultHeaders($parameterBag));

        return $this->easysysConnector->getManager()->execute($parameterBag);
    }
}