<?php

require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/fetch_rates.php';
require_once __DIR__ . '/fetch_history.php';

function requestProcessor($request)
{
    $type = $request['type'] ?? 'UNKNOWN';

    writeLog("Received request: " . $type);

    if (!isset($request['type'])) {
        writeLog("ERROR: Missing request type");

        return [
            'success' => false,
            'error' => 'Missing request type'
        ];
    }

    switch ($request['type']) {

        case 'get_current_rates':
            writeLog("Fetching current rates from Frankfurter API");

            $result = getCurrentRates();

            if ($result['success']) {
                writeLog("Current rates request completed successfully");
            } else {
                writeLog("ERROR: Current rates request failed");
            }

            return $result;

        case 'get_rate_history':
            writeLog("Fetching rate history from Frankfurter API");

            $result = getRateHistory();

            if ($result['success']) {
                $count = count($result['history']);
                writeLog(
                    "Rate history request completed successfully. Rows returned: "
                    . $count
                );
            } else {
                writeLog("ERROR: Rate history request failed");
            }

            return $result;

        default:
            writeLog("ERROR: Unknown request type: " . $request['type']);

            return [
                'success' => false,
                'error' => 'Unknown request type'
            ];
    }
}

?>
