<?php
require_once __DIR__ . '/../broker/log_publisher.php';

function sendToBackend(array $payload): array
{
        $previousFolder = getcwd(); // Save location

        chdir(__DIR__ . '/../lib'); // The ini file is in lib

        require_once('get_host_info.inc');
        require_once('rabbitMQLib.inc');

        $reply = null; // Default reply

        try {
                ob_start(); // Hide any output from the browser

                $client = new rabbitMQClient('frontend_mq.ini', 'frontend');
                $reply = $client->send_request($payload); // Send and wait for reply
        } catch (Exception $e) {
                $reply = null; // Reply is null on any error
        } finally {
                ob_end_clean();
                chdir($previousFolder); // Go back to original folder
        }

        if (is_array($reply)) {
                return $reply;
        }

        $requestType = $payload['type'] ?? 'unknown';

        error_log('Backend communication failed for request: ' . $requestType);

        publishLog(
                'Backend communication failed for request: ' . $requestType,
                'backend_error'
        );

        return array('success' => false, 'error' => 'Service unavailable');
}