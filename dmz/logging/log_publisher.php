<?php
require_once __DIR__ . '/../lib/rabbitMQLib.inc';

function publishLog($message, $type = 'info')
{
    $previousFolder = getcwd();
    chdir(__DIR__ . '/../config');

    $client = new rabbitMQClient('dmz_logging_mq.ini', 'logging');
    $client->publish([
        'source'    => 'dmz',
        'type'      => $type,
        'message'   => $message,
        'timestamp' => date('c')
    ]);

    chdir($previousFolder);
}
