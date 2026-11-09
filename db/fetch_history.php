<?php

require_once "database.php";

$symbols = [
    "USD/JPY",
    "USD/GBP",
    "USD/EUR",
    "USD/CNY",
    "USD/CHF",
    "USD/AED",
    "USD/CAD"
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

    $quoteId = getCurrencyId($conn, $quoteCode);

    foreach ($data as $row) {

        if (!isset($row["date"]) || !isset($row["rate"])) {
            continue;
        }

        $rateDate = $row["date"];
        $rate = $row["rate"];

        $stmt = $conn->prepare(
            "INSERT INTO rate_history (currency_id, rate_date, units_per_usd)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE units_per_usd = VALUES(units_per_usd)"
        );
        $stmt->bind_param("isd", $quoteId, $rateDate, $rate);
        $stmt->execute();
    }

    echo "$symbol history saved" . PHP_EOL;
}

?>
