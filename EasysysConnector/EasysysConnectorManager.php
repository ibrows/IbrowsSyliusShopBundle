<?php

namespace Ibrows\SyliusShopBundle\EasysysConnector;

use EasysysConnector\HttpAdapter\HttpAdapterInterface;
use EasysysConnector\HttpAdapter\HttpResponse;
use EasysysConnector\Manager\Resource\Kb\ResourceOrderManager;
use EasysysConnector\Manager\Resource\Kb\ResourcePositionCustomManager;
use EasysysConnector\Model\Resource\Kb\ResourceOrderInterface;
use EasysysConnector\Model\Resource\Kb\ResourcePositionCustomInterface;
use Remdan\EasysysConnectorBundle\EasysysConnectorManager as BaseEasysysConnectorManager;

class EasysysConnectorManager extends BaseEasysysConnectorManager
{
    /**
     * @param ResourceOrderInterface $cart
     * @return mixed
     */
    public function pushKbOrder(ResourceOrderInterface $cart)
    {
        /** @var ResourceOrderManager $manager */
        $manager = $this->get($cart->getEsResource());
        return $manager->createData($cart);
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
     * @param $recipient
     * @param string $message
     * @param string $subject
     * @param bool $markAsOpen
     * @throws \Exception
     * @return HttpResponse
     */
    public function sendInvoice($invoiceId, $recipient, $message, $subject, $markAsOpen = true)
    {
        $parameterBag = clone $this->getEasysysConnector()->getHttpParameterBag();

        $parameterBag->setMethod(HttpAdapterInterface::HTTP_METHOD_POST);
        $parameterBag->setParameterPostFormat('application/json');
        $parameterBag->setParameterPost(
            array(
                'message'         => $message,
                'recipient_email' => $recipient,
                'subject'         => $subject,
                'mark_as_open'    => $markAsOpen
            )
        );

        $requestUri = (string)vsprintf('kb_invoice/%d/send', array($invoiceId));
        $parameterBag->setUri($requestUri);

        $parameterBag->setHeaders($this->easysysConnector->getAuthAdapter()->getDefaultHeaders($parameterBag));

        return $this->easysysConnector->getManager()->execute($parameterBag);
    }

    /**
     * @param int $invoiceId
     * @param float $invoiceValue
     * @param bool $setFirstToIssued
     * @throws \Exception
     * @return HttpResponse
     */
    public function setInvoiceToPayed($invoiceId, $invoiceValue = null, $setFirstToIssued = true)
    {
        if ($setFirstToIssued) {
            $this->setInvoiceToIssued($invoiceId);
        }

        if (is_null($invoiceValue)) {
            $invoiceValue = $this->getInvoiceValue($invoiceId);
        }

        $parameterBag = clone $this->getEasysysConnector()->getHttpParameterBag();

        $parameterBag->setMethod(HttpAdapterInterface::HTTP_METHOD_POST);
        $parameterBag->setParameterPostFormat('application/json');
        $parameterBag->setParameterPost(
            array(
                'value' => $invoiceValue,
            )
        );

        $requestUri = (string)vsprintf('kb_invoice/%d/payment', array($invoiceId));
        $parameterBag->setUri($requestUri);

        $parameterBag->setHeaders($this->easysysConnector->getAuthAdapter()->getDefaultHeaders($parameterBag));

        return $this->easysysConnector->getManager()->execute($parameterBag);
    }

    /**
     * @param $invoiceId
     * @return HttpResponse
     * @throws \Exception
     */
    public function getInvoiceValue($invoiceId)
    {
        $parameterBag = clone $this->getEasysysConnector()->getHttpParameterBag();

        $parameterBag->setMethod(HttpAdapterInterface::HTTP_METHOD_GET);

        $requestUri = (string)vsprintf('kb_bill/%d', array($invoiceId));
        $parameterBag->setUri($requestUri);

        $parameterBag->setHeaders($this->easysysConnector->getAuthAdapter()->getDefaultHeaders($parameterBag));

        $data = $this->easysysConnector->getManager()->execute($parameterBag);

        $value = 0;
        if (isset($data['positions']) && is_array($data['positions'])) {
            foreach ($data['positions'] as $position) {
                $value += $position['position_total'];
            }
        }
        return $value;
    }

    /**
     * @param int $invoiceId
     * @return HttpResponse
     * @throws \Exception
     */
    public function setInvoiceToIssued($invoiceId)
    {
        $parameterBag = clone $this->getEasysysConnector()->getHttpParameterBag();

        $parameterBag->setMethod(HttpAdapterInterface::HTTP_METHOD_POST);
        $parameterBag->setParameterPostFormat('application/json');

        $requestUri = (string)vsprintf('kb_invoice/%d/issue', array($invoiceId));
        $parameterBag->setUri($requestUri);

        $parameterBag->setHeaders($this->easysysConnector->getAuthAdapter()->getDefaultHeaders($parameterBag));

        return $this->easysysConnector->getManager()->execute($parameterBag);
    }

    /**
     * @param int $invoiceId
     * @return array
     */
    public function getInvoicePayments($invoiceId)
    {
        $parameterBag = clone $this->getEasysysConnector()->getHttpParameterBag();

        $parameterBag->setMethod(HttpAdapterInterface::HTTP_METHOD_GET);

        $requestUri = (string)vsprintf('kb_invoice/%d/payment', array($invoiceId));
        $parameterBag->setUri($requestUri);

        $parameterBag->setHeaders($this->easysysConnector->getAuthAdapter()->getDefaultHeaders($parameterBag));

        $response = $this->easysysConnector->getManager()->execute($parameterBag);
        return json_decode($response->getContent(), true);
    }

    /**
     * @param ResourceOrderInterface $cart
     * @param string $text
     * @return HttpResponse
     */
    public function pushKbComment(ResourceOrderInterface $cart, $text)
    {
        $parameterBag = clone $this->getEasysysConnector()->getHttpParameterBag();

        $parameterBag->setMethod(HttpAdapterInterface::HTTP_METHOD_POST);
        $parameterBag->setParameterPostFormat('application/json');
        $parameterBag->setParameterPost(
            array(
                'user_id'   => $cart->getEsUserId(),
                'text'      => $text,
                'is_public' => false
            )
        );

        $requestUri = (string)vsprintf('kb_order/%d/comment', array($cart->getEsId()));
        $parameterBag->setUri($requestUri);

        $parameterBag->setHeaders($this->easysysConnector->getAuthAdapter()->getDefaultHeaders($parameterBag));

        return $this->easysysConnector->getManager()->execute($parameterBag);
    }
}