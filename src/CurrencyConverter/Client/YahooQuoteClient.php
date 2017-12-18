<?php

namespace FileProcessor\CurrencyConverter\Client;

class YahooQuoteClient extends AbstractClient implements ClientInterface
{
    protected $url = 'http://quote.yahoo.com/d/quotes.csv';

    public function convert($amount, $in, $out)
    {

    }
}
