<?php
require_once __DIR__ . '/../lib/rabbitMQLib.inc';

$machine = 'backend';

chdir(__DIR__ . '/../config');

function saveLog($log)
{
    global $machine;
    file_put_contents("/var/log/forex/$machine.log", json_encode($log) . PHP_EOL, FILE_APPEND);
}

$server = new rabbitMQServer($machine . '_logging_mq.ini', 'logging');
echo "Logging listener BEGIN\n";
$server->process_requests('saveLog');
