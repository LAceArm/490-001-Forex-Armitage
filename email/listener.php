#!/usr/php
<?php
	require_once __DIR__.'/lib/rabbitMQLib.inc';
	require_once __DIR__.'/notification_request_proccessor.php';

	$server=new rabbitMQServer();

	echo "NOTIFICATION LISTENER BEGIN".PHP_EOL;
	$server->process_requests('requestProcessor');
?>
