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
        "INSERT INTO currencies (currency_code, currency_name)
         VALUES (?, ?)
         ON DUPLICATE KEY UPDATE currency_code = currency_code"
    );

    $stmt->bind_param("ss", $code, $code);
    $stmt->execute();

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

foreach ($symbols as $symbol) {

    [$baseCode, $quoteCode] = explode("/", $symbol);

    $url = "https://api.frankfurter.dev/v2/rate/"
         . strtolower($baseCode)
         . "/"
         . strtolower($quoteCode);

    $response = file_get_contents($url);

    if ($response === false) {
        echo "Failed to fetch $symbol" . PHP_EOL;
        continue;
    }

    $data = json_decode($response, true);

    if (!isset($data["rate"])) {
        echo "No rate returned for $symbol" . PHP_EOL;
        continue;
    }

    $rate = $data["rate"];

    $baseId = getCurrencyId($conn, $baseCode);
    $quoteId = getCurrencyId($conn, $quoteCode);

    $stmt = $conn->prepare(
        "INSERT INTO rates
        (base_currency_id, quote_currency_id, rate)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE
            rate = VALUES(rate),
            updated_at = CURRENT_TIMESTAMP"
    );

    $stmt->bind_param(
        "iid",
        $baseId,
        $quoteId,
        $rate
    );

    if ($stmt->execute()) {
        echo "$symbol = $rate saved successfully" . PHP_EOL;
    } else {
        echo "Failed to save $symbol: " . $stmt->error . PHP_EOL;
    }
}

?>
