<?php

namespace FileProcessor\CurrencyConverter\Client;

interface ClientInterface
{
    public function convert($amount, $in, $out);
}
