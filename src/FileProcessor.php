<?php

namespace FileProcessor;

use FileProcessor\CurrencyConverter;

class FileProcessor
{
    protected $file;

    protected $data = array();

    public function __construct(\SplFileObject $file)
    {
        $parser = new FileParser($file);
        $parser->parse();
        $this->data = $parser->getRows();
    }

    public function process()
    {
        $aggregator = new ProductAggregator();
        foreach ($this->data as $data) {
            $lineItem = $this->createLineItem($data);
            $aggregator->addLineItem($lineItem);
        }
        return $aggregator;
    }

    protected function createLineItem(array $data)
    {
        return new LineItem($data['sku'], $data['cost'], $data['price'], $data['qty']);
    }
}
