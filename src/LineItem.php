<?php

namespace FileProcessor;

class LineItem
{
    protected $sku;

    protected $cost;

    protected $price;

    protected $quantity;

    public function __construct($sku, $cost, $price, $quantity)
    {
        $this->sku = $sku;
        $this->cost = $cost;
        $this->price = $price;
        $this->quantity = $quantity;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function getCost()
    {
        return $this->cost;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getSku()
    {
        return $this->sku;
    }

    public function getTotalCost()
    {
        return $this->quantity * $this->cost;
    }

    public function getTotalPrice()
    {
        return $this->quantity * $this->price;
    }
}
