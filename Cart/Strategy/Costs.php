<?php

namespace Ibrows\SyliusShopBundle\Cart\Strategy;

class Costs
{
    /**
     * @var float
     */
    protected $total = 0.0;

    /**
     * @var float
     */
    protected $tax = 0.0;

    /**
     * @var float
     */
    protected $totalWithTax = 0.0;

    /**
     * @return float
     */
    public function getTotalWithTax()
    {
        return $this->totalWithTax;
    }

    /**
     * @param float $totalWithTax
     *
     * @return Costs
     */
    public function setTotalWithTax($totalWithTax)
    {
        $this->totalWithTax = $totalWithTax;

        return $this;
    }

    /**
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param float $tax
     *
     * @return Costs
     */
    public function setTax($tax)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param float $total
     *
     * @return Costs
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }
}
