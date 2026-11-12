<?php

$symbols = [
    "USD/JPY",
    "USD/GBP",
    "USD/EUR",
    "USD/CNY",
    "USD/CHF",
    "USD/AED"
];

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

    $request = [
        "type" => "update_rate",
        "base" => $baseCode,
        "quote" => $quoteCode,
        "rate" => $rate,
        "date" => $data["date"] ?? null
    ];

    print_r($request);
}

?>
