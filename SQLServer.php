#!/usr/bin/php
<?php

require_once('BrokerComms/path.inc');
require_once('BrokerComms/get_host_info.inc');
require_once('BrokerComms/rabbitMQLib.inc');
require_once('auth.php');

function sqlRequestProcessor($request)
{
    echo "received request" . PHP_EOL;
    var_dump($request);

    if (!isset($request['type'])) {
        return [
            "success" => false,
            "error" => "unsupported message type"
        ];
    }

    switch (strtolower($request['type'])) {

	case "login":
   		$result = loginUser(
        	$request['username'],
        	$request['password']
    );

    		var_dump($result);

    return $result;
        case "register":
            return registerUser(
                $request['username'],
                $request['email'],
                $request['password'],
                $request['first_name'] ?? null,
                $request['last_name'] ?? null
            );

        default:
            return [
                "success" => false,
                "error" => "unknown request type"
            ];
    }
}

$server = new rabbitMQServer("SQLMQ.ini", "sqlServer");

echo "SQL/Auth server running" . PHP_EOL;

$server->process_requests('sqlRequestProcessor');

echo "SQL/Auth Server Ended" . PHP_EOL;

exit();

?>
