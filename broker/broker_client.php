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
        // Remember the current directory so it can be restored afterward.
        $prev = getcwd();

        // The course library opens its ini files by bare filename
        // (host.ini, WebServer.ini), so it only finds them from inside the
        // broker/ folder. __DIR__ is the folder this file lives in, so the
        // path works regardless of where Apache or the CLI was started.
        chdir(__DIR__ . '/../lib');

        // Course library:
        //   path.inc            sets PHP's include path
        //   get_host_info.inc   reads the ini files
        //   rabbitMQLib.inc     defines the rabbitMQClient class
        // require_once('path.inc');
        require_once('get_host_info.inc');
        require_once('rabbitMQLib.inc');

        try {
                // The library prints a debug dump of each reply. Capture it
                // so it never reaches the browser; output sent before a
                // header('Location: ...') redirect would break the redirect.
                ob_start();

                // Which ini section to use. Defaults to "broker"; set the
                // BROKER_SECTION environment variable to switch (for example,
                // to point at a different broker while testing).
                $section = getenv('BROKER_SECTION') ?: 'adminServer';

                // Creates a new RabbitMQClient. This is what will communicate with the broker.
                $client = new rabbitMQClient('WebServer.ini', $section);

		// Logs the occurence
		error_log(date("Y/m/d H:i:s") . ": REQUEST TO BROKER " PHP_EOL, 3, "/home/jdk39/WebsiteLogs/Requests.log");

                $reply = $client->send_request($payload);
        } catch (Exception $e) {
                // Something went wrong. Event logged accordingly
		error_log(date("Y/m/d H:i:s") . " REQUEST TO BROKER FAILED: " . $e->getMessage() . PHP_EOL, 3, "/home/jdk39/WebsiteLogs/Requests.log");		
                $reply = null;
        } finally {
                // Runs on success and failure: discard captured output and
                // go back to the original directory.
                ob_end_clean();
                chdir($prev);
        }

        // Callers can always read $r['success'] safely: a non-array reply
        // (null, or a bare string) becomes a standard failure array.
        return is_array($reply)
                ? $reply
                : ['success' => false, 'error' => 'Service unavailable'];
}
