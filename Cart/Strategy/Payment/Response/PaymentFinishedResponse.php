<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response;

class PaymentFinishedResponse
{
    const STATUS_OK = 'ok';
    const STATUS_ERROR = 'error';

    const ERROR_CONFIRMATION = 1;
    const ERROR_COMPLETION = 2;
    const ERROR_VALIDATION = 3;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var int
     */
    protected $errorCode;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param string $status
     * @param int $errorCode
     * @param array $data
     */
    public function __construct($status = self::STATUS_OK, $errorCode = null, array $data = array())
    {
        $this->setStatus($status);
        $this->setErrorCode($errorCode);
        $this->setData($data);
    }

    /**
     * @return null
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param int $errorCode
     * @return PaymentFinishedResponse
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return PaymentFinishedResponse
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return PaymentFinishedResponse
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
}