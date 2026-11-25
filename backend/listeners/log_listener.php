<?php
require_once __DIR__ . '/../lib/rabbitMQLib.inc';

chdir(__DIR__ . '/../config');

function saveLog($log)
{
    file_put_contents("/var/log/forex/backend.log", json_encode($log) . PHP_EOL, FILE_APPEND);
}

$server = new rabbitMQServer('backend_logging_mq.ini', 'logging');
$server->process_requests('saveLog');
