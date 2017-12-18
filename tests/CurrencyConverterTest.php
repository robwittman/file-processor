<?php

namespace FileProcessor\Test;

use FileProcessor\CurrencyConverter;
use FileProcessor\CurrencyConverter\Client\AbstractClient;

class CurrencyConverterTest extends TestCase
{
    public function testConvert()
    {
        $client = $this->getMockBuilder(AbstractClient::class)
            ->setMethods(['convert'])
            ->getMock();
        $client->expects($this->once())
            ->method('convert')
            ->willReturn('1.25');
        $converter = new CurrencyConverter($client);
        $this->assertEquals($converter->convert('1.00', 'USD', 'CAD'), '1.25');
    }
}
