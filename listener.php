#!/usr/bin/php
<?php
/*
 * listener.php  (DB VM)
 *
 * Entry point. Waits for requests from the RabbitMQ broker, passes each to
 * requestProcessor() (which runs the SQL), and sends the result back
 * through the broker as the reply.
 */

// Load code first, using paths relative to this file.
require_once __DIR__ . '/lib/rabbitMQLib.inc';
require_once __DIR__ . '/request_processor.php';

// The course library opens its ini files by bare filename, so move into
// config/ before creating the server.
chdir(__DIR__ . '/config');

$server = new rabbitMQServer('RabbitMQ.ini', 'testServer');
echo "SQL listener BEGIN\n";
$server->process_requests('requestProcessor');
