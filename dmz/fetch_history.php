<?php

function getRateHistory()
{
    $symbols = [
        "USD/JPY",
        "USD/GBP",
        "USD/EUR",
        "USD/CNY",
        "USD/CHF",
        "USD/AED"
    ];

    $endDate = date("Y-m-d");
    $startDate = date("Y-m-d", strtotime("-1 year"));

    $history = [];
    $errors = [];

    foreach ($symbols as $symbol) {

        [$baseCode, $quoteCode] = explode("/", $symbol);

        $url = "https://api.frankfurter.dev/v2/rates"
             . "?base=" . strtolower($baseCode)
             . "&quotes=" . strtolower($quoteCode)
             . "&from=" . $startDate
             . "&to=" . $endDate;

        $response = @file_get_contents($url);

        if ($response === false) {
            $errors[] = "Failed to fetch history for $symbol";
            continue;
        }

        $data = json_decode($response, true);

        if (!is_array($data)) {
            $errors[] = "Invalid history response for $symbol";
            continue;
        }

        foreach ($data as $row) {

            if (!isset($row["date"]) || !isset($row["rate"])) {
                continue;
            }

            $history[] = [
                "base" => $baseCode,
                "quote" => $quoteCode,
                "rate" => $row["rate"],
                "date" => $row["date"]
            ];
        }
    }

    if (empty($history)) {
        return [
            "success" => false,
            "error" => "Unable to retrieve rate history",
            "details" => $errors
        ];
    }

    return [
        "success" => true,
        "history" => $history,
        "errors" => $errors
    ];
}
?>
