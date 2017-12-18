<?php

namespace FileProcessor\CurrencyConverter\Client;

abstract class AbstractClient implements ClientInterface
{
    protected $url;

    public function __construct()
    {
        // Add options?
    }

    public function request(array $options = array())
    {
        $c = $this->getCurlHandle();
        $response = $this->sendCurlRequest($c, $options);
        return $this->parseResponse($response);
    }

    protected function getCurlHandle()
    {
        $c = curl_init();
        curl_setopt_array($c, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_USERAGENT => 'File Processor Sample Requestor'
        ));
        return $c;
    }

    protected function sendCurlRequest($c, array $options = array())
    {
        $url = $this->url;
        if (isset($options['query'])) {
            $url .= '?'.http_build_query($options['query']);
        }
        curl_setopt_array($c, array(
            CURLOPT_URL => $url
        ));
        $res = curl_exec($c);
        $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
        $this->checkResponse($res, $code);
        curl_close($c);
        return $res;
    }

    protected function parseResponse($response)
    {
        return json_decode($response, true);
    }

    protected function checkResponse($res, $code)
    {
        if ($code !== 200) {
            throw new \Exception(
                "Request for currency exchange failed. {$code}:{$res}"
            );
        }
    }
}
