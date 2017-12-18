<?php

namespace FileProcessor\Test;

use FileProcessor\LineItem;

class LineItemTest extends TestCase
{
    public function testGetters()
    {
        $lineItem = new LineItem('1234-abcd', '1.00', '2.00', 4);
        $this->assertEquals($lineItem->getSku(), '1234-abcd');
        $this->assertEquals($lineItem->getCost(), '1.00');
        $this->assertEquals($lineItem->getPrice(), '2.00');
    }
}
