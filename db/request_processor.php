<?php

require_once "authentication.php";
require_once "trading.php"

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

        default:
            return [
                "success" => false,
                "error" => "Unknown request type"
            ];

    }
}
?>
