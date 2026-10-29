<?php

require_once "authentication.php";
require_once "trading.php";
require_once "watchlists.php";

function getRates()
{
    global $conn;

    $sql = "
        SELECT
            b.currency_code AS base_currency,
            q.currency_code AS quote_currency,
            r.rate,
            r.updated_at
        FROM rates r
        JOIN currencies b
            ON r.base_currency_id = b.currency_id
        JOIN currencies q
            ON r.quote_currency_id = q.currency_id
        ORDER BY b.currency_code, q.currency_code
    ";

    $result = $conn->query($sql);

    if (!$result) {
        return [
            "success" => false,
            "error" => $conn->error
        ];
    }

    $rates = [];

    while ($row = $result->fetch_assoc()) {
        $rates[] = $row;
    }

    return [
        "success" => true,
        "rates" => $rates
    ];
}

function getPortfolio($userId)
{
    global $conn;

    $stmt = $conn->prepare("SELECT starting_cash FROM portfolios WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $portfolio = $stmt->get_result()->fetch_assoc();

    if (!$portfolio) {
        return ["success" => false, "error" => "Portfolio not found"];
    }

    $stmt = $conn->prepare(
        "SELECT c.currency_code, p.amount, r.rate
         FROM positions p
         JOIN currencies c ON c.currency_id = p.currency_id
         LEFT JOIN rates r
                ON r.quote_currency_id = p.currency_id
               AND r.base_currency_id = (
                    SELECT currency_id FROM currencies WHERE currency_code = 'USD'
               )
         WHERE p.user_id = ?
         ORDER BY c.currency_code"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $positions = [];
    $totalValue = 0.0;
    $missingRate = false;

    while ($row = $result->fetch_assoc()) {
        $amount = (float)$row["amount"];

        if ($row["currency_code"] === "USD") {
            $usdValue = $amount;
        } elseif ($row["rate"] !== null) {
            $usdValue = $amount / (float)$row["rate"];
        } else {
            $usdValue = null;
            $missingRate = true;
        }

        if ($usdValue !== null) {
            $totalValue += $usdValue;
        }

        $positions[] = [
            "currency"  => $row["currency_code"],
            "amount"    => $amount,
            "usd_value" => $usdValue === null ? null : round($usdValue, 2)
        ];
    }

    $startingCash = (float)$portfolio["starting_cash"];

    return [
        "success"       => true,
        "starting_cash" => $startingCash,
        "current_value" => round($totalValue, 2),
        "gain"          => round($totalValue - $startingCash, 2),
        "positions"     => $positions,
        "incomplete"    => $missingRate
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

        case "register":
            return registerUser(
                $request["username"],
                $request["email"],
                $request["password"],
                $request["first_name"] ?? null,
                $request["last_name"] ?? null
            );

        case "login":
            return loginUser(
                $request["username"],
                $request["password"]
	    );

	case "validate_session":
		return validateSession($request["session_key"] ?? "");

	case "logout":
		return destroySession($request["session_key"] ?? "");

	case "trade":
		$s = validateSession($request["session_key"] ?? "");
		if (empty($s["success"])) {
			return ["success" => false, "error" => "Not logged in"];
		}

		return executeTrade(
			$s["user_id"],
			$request["from"] ?? "",
			$request["to"] ?? "",
			$request["amount"] ?? 0
		);

	case "get_rates":
		return getRates();    

	case "get_portfolio":
		$s = validateSession($request["session_key"] ?? "");
		if (empty($s["success"])) {
                	return ["success" => false, "error" => "Not logged in"];
            	}
            	return getPortfolio($s["user_id"]);
	
        case "get_trades":
                $s = validateSession($request["session_key"] ?? "");
                if (empty($s["success"])) {
                        return ["success" => false, "error" => "Not logged in"];
                }
                return getTrades($s["user_id"]);

        case "get_watchlists":
        case "create_watchlist":
        case "delete_watchlist":
        case "add_watchlist_item":
        case "remove_watchlist_item":
                // All five need a logged-in user; find them from the session key.
                $s = validateSession($request["session_key"] ?? "");
                if (empty($s["success"])) {
                        return ["success" => false, "error" => "Not logged in"];
                }
                $uid  = $s["user_id"];
                $wid  = (int)($request["watchlist_id"] ?? 0);
                $code = $request["code"] ?? "";

                switch ($request["type"]) {
                        case "get_watchlists":        return getWatchlists($uid);
                        case "create_watchlist":      return createWatchlist($uid, $request["name"] ?? "");
                        case "delete_watchlist":      return deleteWatchlist($uid, $wid);
                        case "add_watchlist_item":    return addWatchlistItem($uid, $wid, $code);
                        case "remove_watchlist_item": return removeWatchlistItem($uid, $wid, $code);
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
