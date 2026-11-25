<?php
require_once __DIR__ . '/../lib/rabbitMQLib.inc';

function publishLog($message, $type = 'info')
{
    $previousFolder = getcwd();

    try {
        chdir(__DIR__ . '/../config');

        $client = new rabbitMQClient('frontend_logging_mq.ini', 'logging');
        $client->publish([
            'source'    => 'frontend',
            'type'      => $type,
            'message'   => $message,
            'timestamp' => date('c')
        ]);

    } catch (Throwable $e) {
        error_log('Logging failed: ' . $e->getMessage());

    } finally {
        chdir($previousFolder);
    }
}