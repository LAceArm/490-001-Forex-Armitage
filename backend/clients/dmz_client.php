<?php
function sendToDmz(array $payload): array
{
        $previousFolder = getcwd(); // Save location

        chdir(__DIR__ . '/../config'); // The ini file is in config

        require_once(__DIR__ . '/../lib/get_host_info.inc');
        require_once(__DIR__ . '/../lib/rabbitMQLib.inc');

        $reply = null; // Default reply

        try {
                $client = new rabbitMQClient('backend_dmz.ini', 'backend');
                $reply = $client->send_request($payload); // Send and wait for reply
        } catch (Exception $e) {
                $reply = null; // Reply is null on any error
        } finally {
                chdir($previousFolder); // Go back to original folder
        }

        if (is_array($reply)) {
                return $reply;
        }

        return array('success' => false, 'error' => 'Service unavailable');
}
