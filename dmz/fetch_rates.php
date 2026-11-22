<?php

function getCurrentRates()
{
    $symbols = [
        "USD/JPY",
        "USD/GBP",
        "USD/EUR",
        "USD/CNY",
        "USD/CHF",
        "USD/AED"
    ];

    $rates = [];

    foreach ($symbols as $symbol) {

        [$baseCode, $quoteCode] = explode("/", $symbol);

        $url = "https://api.frankfurter.dev/v2/rate/"
             . strtolower($baseCode)
             . "/"
             . strtolower($quoteCode);

        $response = file_get_contents($url);

        if ($response === false) {
            continue;
        }

        $data = json_decode($response, true);

        if (!isset($data["rate"])) {
            continue;
        }

        $rates[] = [
            "base" => $baseCode,
            "quote" => $quoteCode,
            "rate" => $data["rate"],
            "date" => $data["date"] ?? null
        ];
    }

    return [
        "success" => true,
        "rates" => $rates
    ];
}
?>
