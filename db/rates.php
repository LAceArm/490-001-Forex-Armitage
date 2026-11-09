<?php
// Returns the currency's id and its rate (units per 1 USD), or null if not found
function getCurrencyIdAndRate($currencyCode)
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT currency_id 
        FROM currencies 
        WHERE currency_code = ?"
    );
    $stmt->bind_param("s", $currencyCode);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        return null;
    }

    $currencyId = (int)$row["currency_id"];

    // USD is the base, so 1 USD = 1 USD
    if ($currencyCode === "USD") {
        return ["currency_id" => $currencyId, "units_per_usd" => 1.0];
    }

    $stmt = $conn->prepare(
        "SELECT units_per_usd
        FROM rates
        WHERE currency_id = ?"
    );
    $stmt->bind_param("i", $currencyId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        return null;
    }

    return ["currency_id" => $currencyId, "units_per_usd" => (float)$row["units_per_usd"]];
}

function getRates()
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT c.currency_code, r.units_per_usd
        FROM rates r
        JOIN currencies c ON c.currency_id = r.currency_id
        ORDER BY c.currency_code"
    );
    $stmt->execute();
    $rates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    return ["success" => true, "rates" => $rates];
}

// Latest rate per currency id (units per 1 USD)
function getLatestRates()
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT currency_id, units_per_usd
        FROM rates"
    );
    $stmt->execute();
    $result = $stmt->get_result();

    $rates = [];
    while ($row = $result->fetch_assoc()) {
        $rates[$row["currency_id"]] = (float)$row["units_per_usd"];
    }

    return $rates;
}

// Rate per currency id from the day before the newest day in rate_history
function getPreviousRates()
{
    global $conn;

    // Retrieve all but current day's rate
    $stmt = $conn->prepare(
        "SELECT currency_id, units_per_usd
        FROM rate_history
        WHERE rate_date = (
            SELECT DISTINCT rate_date FROM rate_history
            ORDER BY rate_date DESC)"
    );
    $stmt->execute();
    $result = $stmt->get_result();

    $rates = [];
    while ($row = $result->fetch_assoc()) {
        $rates[$row["currency_id"]] = (float)$row["units_per_usd"];
    }

    return $rates;
}
?>
