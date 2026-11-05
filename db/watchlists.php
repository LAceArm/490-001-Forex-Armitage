<?php
require_once __DIR__ . "/database.php";
require_once __DIR__ . "/rates.php";

function getWatchlists($userId)
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT w.watchlist_id, w.name, c.currency_id, c.currency_code
        FROM watchlists w
        LEFT JOIN watchlist_items wi ON wi.watchlist_id = w.watchlist_id
        LEFT JOIN currencies c ON c.currency_id = wi.currency_id
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
        "DELETE wi FROM watchlist_items wi
        JOIN watchlists w ON w.watchlist_id = wi.watchlist_id
        JOIN currencies c ON c.currency_id = wi.currency_id
        WHERE w.watchlist_id = ? AND w.user_id = ? AND c.currency_code = ?"
    );
    $stmt->bind_param("iis", $watchlistId, $userId, $currencyCode);
    $stmt->execute();

    return ["success" => true];
}