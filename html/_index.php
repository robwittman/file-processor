<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_FILES['file'])) {
$html = <<<HTML
<h4>Upload Product</h4>
<form action='index.php' method='post' enctype='multipart/form-data'>
    File: <input type='file' name='file'>
    <button name='submit' type='submit'>Submit</button>
</form>
HTML;

    echo $html;
    exit;
}

$file = new SplFileObject($_FILES['file']['tmp_name']);
$converter = new CurrencyConverter(new FixerClient());
$processor = new FileProcessor($file);
$aggregate = $processor->process();

function format($amount) {
    return '$'.number_format($amount, 2);
}

?>

<!-- Results HTML -->
<style media="screen">
.currency {
    color: green;
}
.currency[data-amount^="-"] {
    color:red;
}
/* http://cssmenumaker.com/br/blog/stylish-css-tables-tutorial */
table {
    color: #333; /* Lighten up font color */
    font-family: Helvetica, Arial, sans-serif; /* Nicer font */
    width: 640px;
    border-collapse:
    collapse; border-spacing: 0;
}

td, th { border: 1px solid #CCC; height: 30px; } /* Make cells a bit taller */

th {
    background: #F3F3F3; /* Light grey background */
    font-weight: bold; /* Make sure they're bold */
}

td {
    background: #FAFAFA; /* Lighter grey background */
    text-align: center; /* Center our text */
}
</style>
<h4>Results:</h4>
<table summary='Cost / Profit breakdown for products'>
    <thead>
        <tr>
            <th>SKU</th>
            <th>Cost</th>
            <th>Price</th>
            <th>QTY</th>
            <th>Profit Margin</th>
            <th>Total Profit (USD)</th>
            <th>Total Profit (CAD)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($aggregate->getProductResults() as $sku => $result): ?>
            <tr>
                <td>
                    <?php echo $sku; ?>
                </td>
                <td class='currency' data-amount="<?php echo $result['cost']; ?>">
                    <?php echo format($result['cost']); ?>
                </td>
                <td class='currency' data-amount="<?php echo $result['price']; ?>">
                    <?php echo format($result['price']); ?>
                </td>
                <td>
                    <?php echo $result['quantity']; ?>
                </td>
                <td class='currency' data-amount="<?php echo (($result['price'] - $result['cost']) / $result['quantity']); ?>">
                    <?php echo format(($result['price'] - $result['cost']) / $result['quantity']); ?>
                </td>
                <td class='currency' data-amount="<?php echo $result['price'] - $result['cost']; ?>">
                    <?php echo format(($result['price'] - $result['cost'])); ?>
                </td>
                <td class='currency' data-amount="<?php echo $result['price'] - $result['cost']; ?>">
                    <?php echo format($converter->convert(($result['price'] - $result['cost']), 'USD', 'CAD')); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan='4'>Totals</td>
            <td colspan='2'>
                <strong>Avg Price:</strong><br>
                <strong>Total Quantity</strong><br>
                <strong>Avg Profit Margin</strong><br>
                <strong>Total Profit (USD)</strong><br>
                <strong>Total Profit (CAD)</strong><br>
            </td>
            <td>
                <span class='currency' data-amount="<?php echo $aggregate->getAveragePrice(); ?>">
                    <?php echo format($aggregate->getAveragePrice()); ?>
                </span>
                <br>
                <span>
                    <?php echo $aggregate->getTotalQuantity(); ?>
                </span>
                <br>
                <span class='currency' data-amount="<?php echo $aggregate->getAverageProfitMargin(); ?>">
                    <?php echo format($aggregate->getAverageProfitMargin()); ?>
                </span>
                <br>
                <span class='currency' data-amount="<?php echo $aggregate->getTotalProfit(); ?>">
                    <?php echo format($aggregate->getTotalProfit()); ?>
                </span>
                <br>
                <span class='currency' data-amount="<?php echo $converter->convert($aggregate->getTotalProfit(), 'USD', 'CAD'); ?>">
                    <?php echo format($converter->convert($aggregate->getTotalProfit(), 'USD', 'CAD')); ?>
                </span>
                <br>
            </td>
        </tr>
    </tfoot>
</table>
