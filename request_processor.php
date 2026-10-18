<?php

require_once "authentication.php";

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

        default:
            return [
                "success" => false,
                "error" => "Unknown request type"
            ];
    }
}
?>
