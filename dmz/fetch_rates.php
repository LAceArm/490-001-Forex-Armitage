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
    $errors = [];

    foreach ($symbols as $symbol) {

        [$baseCode, $quoteCode] = explode("/", $symbol);

        $url = "https://api.frankfurter.dev/v2/rate/"
             . strtolower($baseCode)
             . "/"
             . strtolower($quoteCode);

        $response = @file_get_contents($url);

        if ($response === false) {
            $errors[] = "Failed to fetch $symbol";
            continue;
        }

        $data = json_decode($response, true);

        if (!is_array($data) || !isset($data["rate"])) {
            $errors[] = "Invalid API response for $symbol";
            continue;
        }

        $rates[] = [
            "base" => $baseCode,
            "quote" => $quoteCode,
            "rate" => $data["rate"],
            "date" => $data["date"] ?? null
        ];
    }

    if (empty($rates)) {
        return [
            "success" => false,
            "error" => "Unable to retrieve currency rates",
            "details" => $errors
        ];
    }

    return [
        "success" => true,
        "rates" => $rates,
        "errors" => $errors
    ];
}
?>
