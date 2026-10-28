<?php

require_once "database.php";

$symbols = [
    "USD/JPY",
    "USD/GBP",
    "USD/EUR",
    "USD/CNY",
    "USD/CHF",
    "USD/AED"
];

function getCurrencyId($conn, $code)
{
    $stmt = $conn->prepare(
        "SELECT currency_id
         FROM currencies
         WHERE currency_code = ?"
    );

    $stmt->bind_param("s", $code);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();

    return $result["currency_id"];
}

$endDate = date("Y-m-d");
$startDate = date("Y-m-d", strtotime("-1 year"));

foreach ($symbols as $symbol) {

    [$baseCode, $quoteCode] = explode("/", $symbol);

    $url = "https://api.frankfurter.dev/v2/rates"
         . "?base=" . strtolower($baseCode)
         . "&quotes=" . strtolower($quoteCode)
         . "&from=" . $startDate
         . "&to=" . $endDate;

    $response = file_get_contents($url);

    if ($response === false) {
        echo "Failed to fetch history for $symbol" . PHP_EOL;
        continue;
    }

    $data = json_decode($response, true);

    $baseId = getCurrencyId($conn, $baseCode);
    $quoteId = getCurrencyId($conn, $quoteCode);

    foreach ($data as $row) {

        if (!isset($row["date"]) || !isset($row["rate"])) {
            continue;
        }

        $rateDate = $row["date"];
        $rate = $row["rate"];

        $stmt = $conn->prepare(
            "INSERT INTO rate_history
            (base_currency_id, quote_currency_id, base_currency_code, quote_currency_code, rate, rate_date)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
		rate = VALUES(rate),
		base_currency_code = VALUES(base_currency_code),
    		quote_currency_code = VALUES(quote_currency_code)"
        );

        $stmt->bind_param(
    	"iissds",
    	$baseId,
    	$quoteId,
    	$baseCode,
    	$quoteCode,
    	$rate,
    	$rateDate
	);

        $stmt->execute();
    }

    echo "$symbol history saved" . PHP_EOL;
}

?>
