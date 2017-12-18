<?php

namespace FileProcessor\CurrencyConverter\Client;

class GoogleFinanceClient extends AbstractClient implements ClientInterface
{
    protected $url = 'https://www.google.com/finance/CurrencyConverter';

    public function convert($amount, $in, $out)
    {

    }
}
