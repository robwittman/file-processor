<?php

namespace FileProcessor\Test;

use FileProcessor\FileParser;

class FileParserTest extends TestCase
{
    /**
     * @dataProvider parseTestGenerator
     */
    public function testParse($file, $headers, $rows)
    {
        $parser = new FileParser(
            new \SplFileObject(
                $this->realpath($file)
            )
        );
        $parser->parse();
        $this->assertEquals($headers, $parser->getHeaders());
        $this->assertEquals($rows, $parser->getRows());
    }

    public function parseTestGenerator()
    {
        return array(
            array(
                '001', array(
                    'sku',
                    'cost',
                    'price',
                    'qty'
                ), array(
                    array(
                        'sku' => '1234',
                        'cost' => '1',
                        'price' => '2',
                        'qty' => 4
                    )
                )
            ),
            array(
                '002', array(
                    'sku',
                    'cost',
                    'price',
                    'qty'
                ), array()
            )
        );
    }

    public function realpath($file)
    {
        return dirname(__FILE__, 2).'/samples/'.$file.'.csv';
    }
}
