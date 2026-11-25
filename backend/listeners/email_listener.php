<?php
require_once __DIR__ . '/../lib/rabbitMQLib.inc';
require_once __DIR__ . '/../processors/mail_processor.php';

chdir(__DIR__ . '/../config');

$server = new rabbitMQServer('email_mq.ini', 'email');
$server->process_requests('SendEmail');
