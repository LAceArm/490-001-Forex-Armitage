<?php

require_once "adzuna_config.php";

$url = "https://api.adzuna.com/v1/api/jobs/us/search/1"
     . "?app_id=" . $appId
     . "&app_key=" . $appKey
     . "&results_per_page=5"
     . "&what=information%20technology"
     . "&where=Newark%2C%20NJ";

$response = file_get_contents("sample_adzuna.json");

if ($response === false) {
    die("Failed to get jobs from Adzuna.\n");
}

$data = json_decode($response, true);

if (!isset($data["results"])) {
    die("No job results found.\n");
}

foreach ($data["results"] as $job) {

    echo "Title: " . ($job["title"] ?? "N/A") . PHP_EOL;

    echo "Company: "
        . ($job["company"]["display_name"] ?? "N/A")
        . PHP_EOL;

    echo "Location: "
        . ($job["location"]["display_name"] ?? "N/A")
        . PHP_EOL;

    echo "Salary: ";

    if (isset($job["salary_min"]) && isset($job["salary_max"])) {
        echo "$" . number_format($job["salary_min"])
           . " - $"
           . number_format($job["salary_max"])
           . PHP_EOL;
    } else {
        echo "Not listed" . PHP_EOL;
    }

    echo "Posted: "
        . ($job["created"] ?? "N/A")
        . PHP_EOL;

    echo "Apply URL: "
        . ($job["redirect_url"] ?? "N/A")
        . PHP_EOL;

    echo "Source: Adzuna" . PHP_EOL;

    echo "-----------------------------------" . PHP_EOL;
}

?>
