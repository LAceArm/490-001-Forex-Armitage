#!/usr/bin/php
<?php
require_once __DIR__ . '/lib/rabbitMQLib.inc';
require_once __DIR__ . '/request_processor.php';

chdir(__DIR__ . '/config');

$server = new rabbitMQServer('backend_mq.ini', 'backend');
echo "SQL listener BEGIN\n";
$server->process_requests('requestProcessor');
