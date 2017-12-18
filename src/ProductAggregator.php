<?php

namespace FileProcessor;

class ProductAggregator
{
    protected $total_cost;

    protected $total_price;

    protected $total_quantity;

    protected $products = array();

    public function addLineItem(LineItem $lineItem)
    {
        $this->assureSku($lineItem->getSku());
        $this->products[$lineItem->getSku()]['quantity'] += $lineItem->getQuantity();
        $this->products[$lineItem->getSku()]['cost'] += $lineItem->getTotalCost();
        $this->products[$lineItem->getSku()]['price'] += $lineItem->getTotalPrice();
        $this->total_cost += $lineItem->getTotalCost();
        $this->total_price += $lineItem->getTotalPrice();
        $this->total_quantity += $lineItem->getQuantity();
    }

    protected function assureSku($sku)
    {
        if (!array_key_exists($sku, $this->products)) {
            $this->products[$sku] = array(
                'quantity' => 0,
                'price' => 0,
                'cost' => 0
            );
        }
    }

    public function getProductResults()
    {
        return $this->products;
    }

    public function getAverageCost()
    {
        return $this->total_cost / $this->total_quantity;
    }

    public function getTotalQuantity()
    {
        return $this->total_quantity;
    }

    public function getTotalProfit()
    {
        return $this->total_price - $this->total_cost;
    }

    public function getAverageProfitMargin()
    {
        return ($this->total_price - $this->total_cost) / $this->total_quantity;
    }

    public function getAveragePrice()
    {
        return $this->total_price / $this->total_quantity;
    }
}
