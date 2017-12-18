<?php

namespace FileProcessor\CurrencyConverter\Client;

class FixerClient extends AbstractClient implements ClientInterface
{
    protected $url = 'http://api.fixer.io/latest';

    protected $rates = array();

    public function convert($amount, $in, $out)
    {
        if (empty($this->rates)) {
            $this->getRates($in);
        }

        if (!isset($this->rates[$out])) {
            throw new \InvalidCurrencyException("Conversion to '{$out}' not provided by Fixer.io");
        }
        $rate = $this->rates[$out];
        return $amount * $rate;
    }

    public function getRates($in)
    {
        $params = array('base' => $in);
        $response = $this->request(array(
            'query' => $params
        ));
        $this->rates = $response['rates'];
    }
}
