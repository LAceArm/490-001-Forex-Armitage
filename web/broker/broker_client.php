<?php
function send_to_broker(array $payload): array
{
        $prev = getcwd();

        chdir(__DIR__ . '/../lib');

        require_once('get_host_info.inc');
        require_once('rabbitMQLib.inc');

        $reply = null;

        try {
                ob_start();

                $client = new rabbitMQClient('WebServer.ini', 'adminServer');
                $reply = $client->send_request($payload);
        } catch (Exception $e) {
                $reply = null;
        } finally {
                ob_end_clean();
                chdir($prev);
        }

        return is_array($reply)
                ? $reply
                : ['success' => false, 'error' => 'Service unavailable'];
}
