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
        "SELECT r.rate
        FROM rates r
        JOIN currencies b ON b.currency_id = r.base_currency_id
        JOIN currencies q ON q.currency_id = r.quote_currency_id
        WHERE b.currency_code = 'USD' AND q.currency_code = ?"
    );
    $stmt->bind_param("s", $currencyCode);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        return null;
    }

    return ["currency_id" => $currencyId, "units_per_usd" => (float)$row["rate"]];
}

function getRates()
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT qc.currency_code,
                r.rate AS units_per_usd,
                r.updated_at
        FROM rates r
        JOIN currencies bc ON bc.currency_id = r.base_currency_id
        JOIN currencies qc ON qc.currency_id = r.quote_currency_id
        WHERE bc.currency_code = 'USD'
        ORDER BY qc.currency_code"
    );
    $stmt->execute();
    $rates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    return [
        "success" => true,
        "rates" => $rates
    ];
}

// Latest rate per currency id (units per 1 USD)
function getLatestRates()
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT r.quote_currency_id AS currency_id, r.rate
        FROM rates r
        JOIN currencies bc ON bc.currency_id = r.base_currency_id
        WHERE bc.currency_code = 'USD'"
    );
    $stmt->execute();
    $result = $stmt->get_result();

    $rates = [];
    while ($row = $result->fetch_assoc()) {
        $rates[$row["currency_id"]] = (float)$row["rate"];
    }

    return $rates;
}

// Rate per currency id from the day before the newest day in rate_history
function getPreviousRates()
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT quote_currency_id AS currency_id, rate
        FROM rate_history
        WHERE base_currency_code = 'USD'
        AND rate_date = (
        SELECT MAX(rate_date) FROM rate_history
        WHERE rate_date < (SELECT MAX(rate_date) FROM rate_history))"
    );
    $stmt->execute();
    $result = $stmt->get_result();

    $rates = [];
    while ($row = $result->fetch_assoc()) {
        $rates[$row["currency_id"]] = (float)$row["rate"];
    }

    return $rates;
}
?>
