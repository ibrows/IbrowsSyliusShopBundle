<?php

namespace Ibrows\SyliusShopBundle\EasysysConnector;


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
        $positionCustom = $this->get($positionCustom->getEsResource())->createData($positionCustom);

        return $positionCustom;
    }

    /**
     * @param CartInterface $cart
     * @param $text
     * @return HttpResponse
     */
    public function pushKbComment(CartInterface $cart, $text)
    {
        $parameterBag = new HttpParameterBag();
        $parameterBag->setMethod(HttpAdapterInterface::HTTP_METHOD_POST);
        $parameterBag->setParameterPostFormat('application/json');
        $parameterBag->setParameterPost(array(
            'user_id' => $cart->getEsUserId(),
            'text' => $text
        ));

        $requestUri = (string)vsprintf('kb_order/%s/comment', array($cart->getEsId()));
        $parameterBag->setUri($requestUri);

        $parameterBag->setHeaders($this->easysysConnector->getAuthAdapter()->getDefaultHeaders($parameterBag));

        return $this->easysysConnector->getManager()->execute($parameterBag);
    }
}