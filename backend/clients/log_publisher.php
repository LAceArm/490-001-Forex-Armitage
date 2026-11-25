<?php
require_once __DIR__ . '/../lib/rabbitMQLib.inc';

function publishLog($message, $type = 'info')
{
    $previousFolder = getcwd();
    chdir(__DIR__ . '/../config');

    try {
        $client = new rabbitMQClient('backend_logging_mq.ini', 'logging');
        $client->publish([
            'source'    => 'backend',
            'type'      => $type,
            'message'   => $message,
            'timestamp' => date('c')
        ]);
    } catch (throwable $e) {
        error_log('Logging failed: ' . $e->getMessage());
    } finally {
        chdir($previousFolder);
    }
}
