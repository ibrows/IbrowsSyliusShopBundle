<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy\Payment\Response;

class PaymentFinishedResponse
{
    const STATUS_OK = 'ok';
    const STATUS_ERROR = 'error';
    const STATUS_CANCEL = 'cancel';

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
    protected $strategyId;

    /**
     * @var array
     */
    protected $strategyData;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param $strategyId
     * @param string $status
     * @param null $errorCode
     * @param array $strategyData
     * @param array $data
     */
    public function __construct($strategyId, $status = self::STATUS_OK, $errorCode = null, array $strategyData = array(), array $data = array())
    {
        $this->status = $status;
        $this->errorCode = $errorCode;
        $this->strategyId = $strategyId;
        $this->strategyData = $strategyData;
        $this->data = $data;
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
     * @return string
     */
    public function getStrategyId()
    {
        return $this->strategyId;
    }

    /**
     * @param string $strategyId
     * @return PaymentFinishedResponse
     */
    public function setStrategyId($strategyId)
    {
        $this->strategyId = $strategyId;
        return $this;
    }

    /**
     * @return array
     */
    public function getStrategyData()
    {
        return $this->strategyData;
    }

    /**
     * @param array $strategyData
     * @return PaymentFinishedResponse
     */
    public function setStrategyData($strategyData)
    {
        $this->strategyData = $strategyData;
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