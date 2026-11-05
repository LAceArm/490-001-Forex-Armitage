<?php
require_once "database.php";

// Latest rate per currency id (units per 1 USD)
function getLatestRates()
{
    global $conn;

    $rates = [];
    $res = $conn->query(
        "SELECT r.quote_currency_id AS id, r.rate
        FROM rates r
        JOIN currencies b ON b.currency_id = r.base_currency_id
        WHERE b.currency_code = 'USD'"
    );

    while ($row = $res->fetch_assoc()) {
        $rates[$row["watchlist_id"]] = (float)$row["rate"];
    }

    return $rates;
}

// Rate per currency id from the day before the newest day in rate_history
function getPreviousRates()
{
    global $conn;

    $rates = [];
    $res = $conn->query(
        "SELECT quote_currency_id AS id, rate
        FROM rate_history
        WHERE base_currency_code = 'USD'
        AND rate_date = (
        SELECT MAX(rate_date) FROM rate_history
        WHERE rate_date < (SELECT MAX(rate_date) FROM rate_history))"
    );

    while ($row = $res->fetch_assoc()) {
        $rates[$row["watchlist_id"]] = (float)$row["rate"];
    }

    return $rates;
}

function getWatchlists($userId)
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT w.watchlist_id, w.name, c.currency_id, c.currency_code
        FROM watchlists w
        LEFT JOIN watchlist_items i ON i.watchlist_id = w.watchlist_id
        LEFT JOIN currencies c ON c.currency_id = i.currency_id
        WHERE w.user_id = ?
        ORDER BY w.watchlist_id, c.currency_code"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $latest = getLatestRates();
    $previous = getPreviousRates();
    $lists = [];

    while ($row = $result->fetch_assoc()) {
        $watchlistId = (int)$row["watchlist_id"];

        // First row for this watchlist: start its entry
        if (!isset($lists[$watchlistId])) {
            $lists[$watchlistId] = ["watchlist_id" => $watchlistId, "name" => $row["name"], "currencies" => []];
        }

        // A watchlist with no currencies comes back with a null currency
        if ($row["currency_id"] === null) {
            continue;
        }

        $currencyId = (int)$row["currency_id"];
        $latestRate = $latest[$currencyId] ?? null;
        $previousRate = $previous[$currencyId] ?? null;

        $lists[$watchlistId]["currencies"][] = [
            "currency_code" => $row["currency_code"],
            "usd_per_unit" => $latestRate ? 1 / $latestRate : null,
            "change_percent" => ($latestRate && $previousRate) ? ($previousRate / $latestRate - 1) * 100 : null
        ];
    }
    return ["success" => true, "watchlists" => array_values($lists)];
}

// Creates an empty list with the given name.
function createWatchlist($userId, $name)
{
    global $conn;

    $name = trim($name);
    if ($name === "" || strlen($name) > 50) {
        return ["success" => false, "error" => "Enter a name up to 50 characters"];
    }

    $stmt = $conn->prepare("INSERT INTO watchlists (user_id, name) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $name);
    $stmt->execute();

    return ["success" => true, "watchlist_id" => $conn->insert_id];
}

// Deletes one of this user's lists. Its items go with it (ON DELETE CASCADE).
function deleteWatchlist($userId, $watchlistId)
{
    global $conn;

    $stmt = $conn->prepare("DELETE FROM watchlists WHERE watchlist_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $watchlistId, $userId);
    $stmt->execute();

    return ["success" => $stmt->affected_rows > 0];
}

// Adds a currency to one of this user's lists. Adding it twice does nothing.
function addWatchlistItem($userId, $watchlistId, $currencyCode)
{
    global $conn;

    $currencyCode = strtoupper(trim($currencyCode));

    // The SELECT only returns a row if the list is this user's and the
    // currency exists, so otherwise nothing is inserted.
    $stmt = $conn->prepare(
        "INSERT IGNORE INTO watchlist_items (watchlist_id, currency_id)
        SELECT w.watchlist_id, c.currency_id
        FROM watchlists w, currencies c
        WHERE w.watchlist_id = ? AND w.user_id = ? AND c.currency_code = ?"
    );
    $stmt->bind_param("iis", $watchlistId, $userId, $currencyCode);
    $stmt->execute();

    return ["success" => true];
}

// Removes a currency from one of this user's lists.
function removeWatchlistItem($userId, $watchlistId, $currencyCode)
{
    global $conn;

    $currencyCode = strtoupper(trim($currencyCode));

    $stmt = $conn->prepare(
        "DELETE i FROM watchlist_items i
        JOIN watchlists w ON w.watchlist_id = i.watchlist_id
        JOIN currencies c ON c.currency_id = i.currency_id
        WHERE w.watchlist_id = ? AND w.user_id = ? AND c.currency_code = ?"
    );
    $stmt->bind_param("iis", $watchlistId, $userId, $currencyCode);
    $stmt->execute();

    return ["success" => true];
}

