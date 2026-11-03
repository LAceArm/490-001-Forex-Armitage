<?php
function send_to_broker(array $payload): array
{
        $prev = getcwd(); // Save location

        chdir(__DIR__ . '/../lib'); // Change location to access library folder

	// Library files
        require_once('get_host_info.inc');
        require_once('rabbitMQLib.inc');

        $reply = null; // Default reply

        try {
                ob_start(); // Initialize bucket, hides content from browser

                $client = new rabbitMQClient('WebServer.ini', 'adminServer'); // Create RabbitMQClient
                $reply = $client->send_request($payload); // Sends request through broker, waits for reply
        } catch (Exception $e) {
                $reply = null; // Reply is null on any error
        } finally {
                ob_end_clean(); // Empty bucket
                chdir($prev); // Revert to original folder
        }

	// If database sends back array, pass along to page
	if (is_array($reply)) {
                return $reply;
	}

	// If nothing comes back, pass failure along to page
	return array('success' => false, 'error' => 'Service unavailable');
}
