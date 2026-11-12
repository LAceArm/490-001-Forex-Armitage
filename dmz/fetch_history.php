<?php

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

    foreach ($data as $row) {

        if (!isset($row["date"]) || !isset($row["rate"])) {
            continue;
        }

        $request = [
            "type" => "update_history",
            "base" => $baseCode,
            "quote" => $quoteCode,
            "rate" => $row["rate"],
            "date" => $row["date"]
        ];

        print_r($request);
    }
}

?>
