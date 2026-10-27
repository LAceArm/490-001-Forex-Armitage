<?php
/*
 * broker_client.php  (Web VM)
 *
 * Sends one request to the RabbitMQ broker and returns the reply.
 *
 * Always returns an array. On any failure it returns:
 *     ['success' => false, 'error' => 'Service unavailable']
 */

function send_to_broker(array $payload): array
{
        $prev = getcwd();

        chdir(__DIR__ . '/../lib');

        require_once('get_host_info.inc');
        require_once('rabbitMQLib.inc');

        try {
                ob_start();

                $section = getenv('BROKER_SECTION') ?: 'adminServer';

                // Creates a new RabbitMQClient. This is what will communicate with the broker.
                $client = new rabbitMQClient('WebServer.ini', $section);

		// Logs the occurence
		error_log(date("Y/m/d H:i:s") . ": REQUEST TO BROKER ". PHP_EOL, 3, "/home/jdk39/WebsiteLogs/BrokerLogs.log");

                $reply = $client->send_request($payload);
        } catch (Exception $e) {
                // Something went wrong. Event logged accordingly
	error_log(date("Y/m/d H:i:s") . ": REQUEST TO BROKER FAILED: " . $e->getMessage() . PHP_EOL, 3, "/home/jdk39/WebsiteLogs/BrokerLogs.log");		

               $reply=null; // No response.
	}
        finally {
                ob_end_clean();
                chdir($prev);
        }

        return is_array($reply)
                ? $reply
                : ['success' => false, 'error' => 'Service unavailable'];
}
