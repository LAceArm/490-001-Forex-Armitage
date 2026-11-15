<?php
require_once __DIR__ . "/authentication.php";
require_once __DIR__ . "/trading.php";
require_once __DIR__ . "/portfolio.php";
require_once __DIR__ . "/rates.php";
require_once __DIR__ . "/watchlists.php";

function requestProcessor($request)
{
    if (!isset($request["type"])) {
        return ["success" => false, "error" => "Missing request type"];
    }

    // These work without logging in; every other type needs a valid session
    $openTypes = ["login", "register", "validate_session", "logout", "get_rates"];
    
    if (!in_array($request["type"], $openTypes)) {
        $session = validateSession($request["session_key"] ?? "");

        if (!$session["success"]) {
            return ["success" => false, "error" => "Not logged in"];
        }
    
        $userId = $session["user_id"];
    }

    switch ($request["type"]) {
        /* Authentication */
        case "login":
            return loginUser($request["username"], $request["password"]);

        case "register":
            return registerUser($request["username"], $request["email"], $request["password"]);

        case "validate_session":
            return validateSession($request["session_key"] ?? "");

        case "logout":
            return destroySession($request["session_key"] ?? "");

        /* Rates, Portfolio, Trading */
        case "get_rates":
            return getRates();
        
        case "get_portfolio":
            return getPortfolio($userId);

        case "get_trades":
            return getTrades($userId);

        case "trade":
            return executeTrade(
                $userId,
                $request["from_code"] ?? "",
                $request["to_code"] ?? "",
                $request["from_amount"] ?? 0
            );

        /* Watchlists */
        case "get_watchlists":
            return getWatchlists($userId);
        
        case "create_watchlist":
            return createWatchlist($userId, $request["name"] ?? "");

        case "delete_watchlist":
            return deleteWatchlist($userId, (int)($request["watchlist_id"] ?? 0));

        case "add_watchlist_item":
            return addWatchlistItem(
                $userId,
                (int)($request["watchlist_id"] ?? 0),
                $request["currency_code"] ?? ""
            );
        
        case "remove_watchlist_item":
            return removeWatchlistItem(
                $userId,
                (int)($request["watchlist_id"] ?? 0),
                $request["currency_code"] ?? ""
            );

        default:
            return ["success" => false, "error" => "Unknown request type"];
    }
}
?>
