<?php
require_once __DIR__ . "/authentication.php";
require_once __DIR__ . "/trading.php";
require_once __DIR__ . "/rates.php";
require_once __DIR__ . "/watchlists.php";

function getPortfolio($userId)
{
    global $conn;

    $stmt = $conn->prepare("SELECT starting_cash FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $portfolio = $stmt->get_result()->fetch_assoc();

    $stmt = $conn->prepare(
        "SELECT c.currency_code,
            p.amount,
            IF(c.currency_code = 'USD', p.amount, p.amount / r.units_per_usd) AS usd_value
        FROM positions p
        JOIN currencies c ON c.currency_id = p.currency_id
        LEFT JOIN rates r ON r.currency_id = p.currency_id
        WHERE p.user_id = ?"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $holdings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    return [
        "success" => true,
        "starting_cash" => $portfolio["starting_cash"],
        "holdings" => $holdings
    ];
}

function requestProcessor($request)
{
    if (!isset($request["type"])) {
        return [
            "success" => false,
            "error" => "Missing request type"
        ];
    }

    switch ($request["type"]) {
        /* Authentication */
        case "login":
            return loginUser(
                $request["username"],
                $request["password"]
	        );

        case "register":
            return registerUser(
                $request["username"],
                $request["email"],
                $request["password"]
            );

        case "validate_session":
            return validateSession($request["session_key"] ?? "");

        case "logout":
            return destroySession($request["session_key"] ?? "");

        /* Forex logic */
        case "trade":
            $session = validateSession($request["session_key"] ?? "");
            if (empty($session["success"])) {
                return ["success" => false, "error" => "Not logged in"];
            }

            return executeTrade(
                $session["user_id"],
                $request["from_code"] ?? "",
                $request["to_code"] ?? "",
                $request["from_amount"] ?? 0
            );

        case "get_trades":
            $session = validateSession($request["session_key"] ?? "");
            if (empty($session["success"])) {
                return ["success" => false, "error" => "Not logged in"];
            }
            return getTrades($session["user_id"]);

        case "get_rates":
            return getRates();

        case "get_portfolio":
            $session = validateSession($request["session_key"] ?? "");
            if (empty($session["success"])) {
                return ["success" => false, "error" => "Not logged in"];
            }
            return getPortfolio($session["user_id"]);

        case "get_watchlists":
        case "create_watchlist":
        case "delete_watchlist":
        case "add_watchlist_item":
        case "remove_watchlist_item":
            // All five need a logged-in user; find them from the session key.
            $session = validateSession($request["session_key"] ?? "");
            if (empty($session["success"])) {
                return ["success" => false, "error" => "Not logged in"];
            }
            $userId       = $session["user_id"];
            $watchlistId  = (int)($request["watchlist_id"] ?? 0);
            $currencyCode = $request["currency_code"] ?? "";

            switch ($request["type"]) {
                case "get_watchlists":        return getWatchlists($userId);
                case "create_watchlist":      return createWatchlist($userId, $request["name"] ?? "");
                case "delete_watchlist":      return deleteWatchlist($userId, $watchlistId);
                case "add_watchlist_item":    return addWatchlistItem($userId, $watchlistId, $currencyCode);
                case "remove_watchlist_item": return removeWatchlistItem($userId, $watchlistId, $currencyCode);
            }
            break;

        default:
            return [
                "success" => false,
                "error" => "Unknown request type"
            ];

    }
}
?>
