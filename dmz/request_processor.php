<?php
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/fetch_rates.php';
require_once __DIR__ . '/fetch_history.php';

function requestProcessor($request)
{
    writeLog("Received request: " . ($request['type'] ?? 'UNKNOWN'));

    if (!isset($request['type'])) {
        writeLog("Error: Missing request type");

        return [
            'success' => false,
            'error' => 'Missing request type'
        ];
    }

    switch ($request['type']) {
        case 'get_current_rates':
            writeLog("Fetching current rates from Frankfurter API");
            return getCurrentRates();

        case 'get_rate_history':
            writeLog("Fetching rate history from Frankfurter API");
            return getRateHistory();

        default:
            writeLog("Error: Unknown request type - " . $request['type']);

            return [
                'success' => false,
                'error' => 'Unknown request type'
            ];
    }
}
?>
