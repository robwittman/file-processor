<?php

namespace FileProcessor;

use FileProcessor\CurrencyConverter\Client\ClientInterface;
use FileProcessor\CurrencyConverter\Client\FixerClient;

class CurrencyConverter
{
    const CURRENCY_USD = 'USD';
    const CURRENCY_CAD = 'CAD';

    protected $client;

    public function __construct(ClientInterface $client = null)
    {
        if (is_null($client)) {
            $this->client = $this->getDefaultClient();
        } else {
            $this->client = $client;
        }
    }

    public function convert($amount, $in, $out)
    {
        $result = $this->getClient()->convert($amount, $in, $out);
        return $result;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getDefaultClient()
    {
        return new FixerClient();
    }
}
