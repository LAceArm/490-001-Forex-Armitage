<?php

require_once __DIR__ . '/logging/log_publisher.php';
require_once __DIR__ . '/fetch_rates.php';
require_once __DIR__ . '/fetch_history.php';

function requestProcessor($request)
{
    $type = $request['type'] ?? 'UNKNOWN';

    publishLog("Received request: " . $type);

    if (!isset($request['type'])) {
        publishLog("ERROR: Missing request type");

        return [
            'success' => false,
            'error' => 'Missing request type'
        ];
    }

    switch ($request['type']) {

        case 'get_current_rates':
            publishLog("Fetching current rates from Frankfurter API");

            $result = getCurrentRates();

            if ($result['success']) {
                publishLog("Current rates request completed successfully");
            } else {
                publishLog("ERROR: Current rates request failed");
            }

            return $result;

        case 'get_rate_history':
            publishLog("Fetching rate history from Frankfurter API");

            $result = getRateHistory();

            if ($result['success']) {
                $count = count($result['history']);
                publishLog(
                    "Rate history request completed successfully. Rows returned: "
                    . $count
                );
            } else {
                publishLog("ERROR: Rate history request failed");
            }

            return $result;

        default:
            publishLog("ERROR: Unknown request type: " . $request['type']);

            return [
                'success' => false,
                'error' => 'Unknown request type'
            ];
    }
}

?>
