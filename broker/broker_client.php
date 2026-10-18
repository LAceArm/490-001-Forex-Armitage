<?php
/*
 * broker_client.php  (Web VM)
 *
 * Sends one request to the RabbitMQ broker and returns the reply.
 * Every page (login, register, home) calls send_to_broker() instead of
 * talking to RabbitMQ itself. The web VM never connects to MySQL; it only
 * asks the DB listener a question through the broker and gets an answer back.
 *
 * Usage:
 *     $r = send_to_broker(['type' => 'login', 'username' => $u, 'password' => $p]);
 *     if (!empty($r['success'])) { ... }
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
        chdir(__DIR__ . '/../broker');

        // Course library:
        //   path.inc            sets PHP's include path
        //   get_host_info.inc   reads the ini files
        //   rabbitMQLib.inc     defines the rabbitMQClient class
        require_once('path.inc');
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
                $section = getenv('BROKER_SECTION') ?: 'broker';

                // Reads host, port, user, password, vhost, exchange and queue
                // from that section of WebServer.ini.
                $client = new rabbitMQClient('WebServer.ini', $section);

                // Publishes $payload as JSON, tagged with a reply queue and
                // a correlation id, then blocks until the matching reply
                // arrives. The reply comes back already decoded.
                $reply = $client->send_request($payload);
        } catch (Throwable $e) {
                // Broker down, bad credentials, wrong vhost, and so on.
                // The real cause is hidden from the page on purpose; uncomment
                // the next line while debugging to write it to Apache's
                // error log (/var/log/apache2/error.log).
                // error_log('send_to_broker: ' . $e->getMessage());
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
