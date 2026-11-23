<?php
require_once __DIR__ . '/../lib/rabbitMQLib.inc';

$machine = 'dmz';

function publishLog($message, $type = 'info')
{
    global $machine;

    $previousFolder = getcwd();
    chdir(__DIR__ . '/../config');

    $client = new rabbitMQClient($machine . '_logging_mq.ini', 'logging');
    $client->publish([
        'source'    => $machine,
        'type'      => $type,
        'message'   => $message,
        'timestamp' => date('c')
    ]);

    chdir($previousFolder);
}
