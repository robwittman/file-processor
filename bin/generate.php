<?php

require_once '../vendor/autoload.php';

$totals = array(
    'quantity' => 0,
    'price' => 0,
    'cost' => 0
);
$faker = Faker\Factory::create();

if (file_exists('../output.csv')) {
    unlink('../output.csv');
}
$data = array(array(
    'sku',
    'cost',
    'price',
    'qty'
));

$numberOfProducts = 25;
for ($i = 0; $i < $numberOfProducts; $i++) {
    $sku = $faker->ean13;
    $product = [
        'sku' => $sku,
        'cost' => $faker->randomDigit(),
        'price' => $faker->randomDigit()
    ];
    $dupes = $faker->randomDigit();
    $x = 0;
    while ($x++ < $dupes) {
        $quantity = $faker->randomDigit();
        $data[] = array_merge($product, array('quantity' => $quantity));
        $totals['quantity'] += $quantity;
        $totals['cost'] += $product['cost'] * $quantity;
        $totals['price'] += $product['price'] * $quantity;
    }
}

file_put_contents('../output.csv', implode(PHP_EOL, array_map(function($row) {
    return implode(',', array_values($row));
}, $data)));

error_log("Expected totals");
error_log(json_encode($totals), JSON_PRETTY_PRINT);
