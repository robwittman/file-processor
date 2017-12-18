<?php



class InvalidCurrencyException extends \Exception
{
    
}


class LineItem
{
    protected $sku;

    protected $cost;

    protected $price;

    protected $quantity;

    public function __construct($sku, $cost, $price, $quantity)
    {
        $this->sku = $sku;
        $this->cost = $cost;
        $this->price = $price;
        $this->quantity = $quantity;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function getCost()
    {
        return $this->cost;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getSku()
    {
        return $this->sku;
    }

    public function getTotalCost()
    {
        return $this->quantity * $this->cost;
    }

    public function getTotalPrice()
    {
        return $this->quantity * $this->price;
    }
}


class ProductAggregator
{
    protected $total_cost;

    protected $total_price;

    protected $total_quantity;

    protected $products = array();

    public function addLineItem(LineItem $lineItem)
    {
        $this->assureSku($lineItem->getSku());
        $this->products[$lineItem->getSku()]['quantity'] += $lineItem->getQuantity();
        $this->products[$lineItem->getSku()]['cost'] += $lineItem->getTotalCost();
        $this->products[$lineItem->getSku()]['price'] += $lineItem->getTotalPrice();
        $this->total_cost += $lineItem->getTotalCost();
        $this->total_price += $lineItem->getTotalPrice();
        $this->total_quantity += $lineItem->getQuantity();
    }

    protected function assureSku($sku)
    {
        if (!array_key_exists($sku, $this->products)) {
            $this->products[$sku] = array(
                'quantity' => 0,
                'price' => 0,
                'cost' => 0
            );
        }
    }

    public function getProductResults()
    {
        return $this->products;
    }

    public function getAverageCost()
    {
        return $this->total_cost / $this->total_quantity;
    }

    public function getTotalQuantity()
    {
        return $this->total_quantity;
    }

    public function getTotalProfit()
    {
        return $this->total_price - $this->total_cost;
    }

    public function getAverageProfitMargin()
    {
        return ($this->total_price - $this->total_cost) / $this->total_quantity;
    }

    public function getAveragePrice()
    {
        return $this->total_price / $this->total_quantity;
    }
}


interface ClientInterface
{
    public function convert($amount, $in, $out);
}


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


class GoogleFinanceClient extends AbstractClient implements ClientInterface
{
    protected $url = 'https://www.google.com/finance/CurrencyConverter';

    public function convert($amount, $in, $out)
    {

    }
}


class YahooQuoteClient extends AbstractClient implements ClientInterface
{
    protected $url = 'http://quote.yahoo.com/d/quotes.csv';

    public function convert($amount, $in, $out)
    {

    }
}



class CurrencyConverter
{
    const CURRENCY_USD = 'USD';
    const CURRENCY_CAD = 'CAD';

    protected $client;

    public function __construct(ClientInterface $client = null)
    {
        if (is_null($client)) {
            $this->client = $this->getDefaultClient();
        } else {
            $this->client = $client;
        }
    }

    public function convert($amount, $in, $out)
    {
        $result = $this->getClient()->convert($amount, $in, $out);
        return $result;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getDefaultClient()
    {
        return new FixerClient();
    }
}


class FileParser
{
    protected $file;

    protected $headers = array();

    protected $rows = array();

    public function __construct(\SplFileObject $file)
    {
        $this->file = $file;
    }

    public function parse()
    {
        $this->headers = $this->file->fgetcsv();
        while (!$this->file->eof()) {
            $data = $this->file->fgetcsv();
            $this->rows[] = array_change_key_case(array_combine($this->headers, $data), CASE_LOWER);
        }
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getRows()
    {
        return $this->rows;
    }
}



class FileProcessor
{
    protected $file;

    protected $data = array();

    public function __construct(\SplFileObject $file)
    {
        $parser = new FileParser($file);
        $parser->parse();
        $this->data = $parser->getRows();
    }

    public function process()
    {
        $aggregator = new ProductAggregator();
        foreach ($this->data as $data) {
            $lineItem = $this->createLineItem($data);
            $aggregator->addLineItem($lineItem);
        }
        return $aggregator;
    }

    protected function createLineItem(array $data)
    {
        return new LineItem($data['sku'], $data['cost'], $data['price'], $data['qty']);
    }
}
?>

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
