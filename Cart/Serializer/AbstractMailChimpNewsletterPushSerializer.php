<?php

namespace Ibrows\SyliusShopBundle\Cart\Serializer;

use Ibrows\SyliusShopBundle\Model\Cart\CartInterface;
use Ibrows\SyliusShopBundle\Model\Cart\Serializer\CartSerializerInterface;
use Payment\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractMailChimpNewsletterPushSerializer implements CartSerializerInterface
{
    /**
     * @var HttpClientInterface
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var array
     */
    protected $listIds = array();

    /**
     * @var string
     */
    protected $endPointSchema;

    /**
     * @var bool
     */
    protected $testMode = false;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param HttpClientInterface $httpClient
     * @param string $apiKey
     * @param array $listIds
     * @param string $endPointSchema
     */
    public function __construct(HttpClientInterface $httpClient, $apiKey, array $listIds = array(), $endPointSchema = 'https://{{datacenter}}.api.mailchimp.com/2.0/')
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
        $this->listIds = $listIds;
        $this->endPointSchema = $endPointSchema;
    }

    /**
     * @param CartInterface $cart
     * @return void
     */
    public function serialize(CartInterface $cart)
    {
        foreach($this->getEmailsToPush($cart) as $listId => $email){
            $this->subscribe($listId, $email);
        }
    }

    /**
     * @param int $listId
     * @param string $email
     */
    protected function subscribe($listId, $email)
    {
        $logger = $this->getLogger();

        $url = $this->getEndpoint().'/lists/subscribe.json';
        $data = array(
            'apikey' => $this->getApiKey(),
            'id' => $listId,
            'email' => array(
                'email' => $email
            )
        );

        if(!$this->isTestMode()){
            $httpClient = $this->httpClient;
            $response = $httpClient->request(
                $httpClient::METHOD_POST,
                $url,
                json_encode($data)
            );

            $logger->info('MailChimp email add '. $email .' to list #'. $listId .' / Response: '. json_encode(array(
                'statusCode' => $response->getStatusCode(),
                'content' => json_decode($response->getContent())
            )));
        }else{
            $logger->info('MailChimp email add '. $email .' to list #'. $listId .' (TestMode) / Request: '. json_encode(array(
                'url' => $url,
                'data' => $data
            )));
        }
    }

    /**
     * @return boolean
     */
    public function isTestMode()
    {
        return $this->testMode;
    }

    /**
     * @param boolean $testMode
     * @return AbstractMailChimpNewsletterPushSerializer
     */
    public function setTestMode($testMode)
    {
        $this->testMode = $testMode;
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->logger ?: new NullLogger();
    }

    /**
     * @param LoggerInterface $logger
     * @return AbstractMailChimpNewsletterPushSerializer
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     * @return AbstractMailChimpNewsletterPushSerializer
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndPointSchema()
    {
        return $this->endPointSchema;
    }

    /**
     * @param string $endPointSchema
     * @return AbstractMailChimpNewsletterPushSerializer
     */
    public function setEndPointSchema($endPointSchema)
    {
        $this->endPointSchema = $endPointSchema;
        return $this;
    }

    /**
     * @param string $schema
     * @param string $apiKey
     * @return string
     */
    protected function getEndpoint($schema = null, $apiKey = null)
    {
        $schema = $schema ?: $this->getEndPointSchema();
        return str_replace('{{datacenter}}', $this->getDatacenter($apiKey), $schema);
    }

    /**
     * @param string $apiKey
     * @return string
     */
    protected function getDatacenter($apiKey = null)
    {
        $apiKey = $apiKey ?: $this->getApiKey();
        $explode = explode("-", $apiKey);
        return isset($explode[1]) ? $explode[1] : null;
    }

    /**
     * Override for giving correct mail addresses
     *
     * @param CartInterface $cart
     * @return array [mailChimpListId] => email
     */
    protected function getEmailsToPush(CartInterface $cart)
    {
        $emails = array();
        foreach($this->getListIds() as $listId){
            $emails[$listId] = $cart->getEmail();
        }
        return $emails;
    }

    /**
     * @return array
     */
    public function getListIds()
    {
        return $this->listIds;
    }

    /**
     * @param array $listIds
     * @return AbstractMailChimpNewsletterPushSerializer
     */
    public function setListIds($listIds)
    {
        $this->listIds = $listIds;
        return $this;
    }
}