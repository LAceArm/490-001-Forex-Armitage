<?php
// Returns the currency's id and its rate (units per 1 USD), or null if not found
function getCurrencyInfo($currencyCode)
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
?>
