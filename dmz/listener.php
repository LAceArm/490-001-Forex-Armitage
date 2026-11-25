<?php

require_once __DIR__ . '/logging/log_publisher.php';
require_once __DIR__ . '/lib/rabbitMQLib.inc';
require_once __DIR__ . '/request_processor.php';

chdir(__DIR__ . '/config');

publishLog("DMZ listener started");

$server = new rabbitMQServer('dmz_mq.ini', 'dmz');

echo "DMZ listener BEGIN\n";

$server->process_requests('requestProcessor');

?>
