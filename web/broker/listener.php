#!/usr/bin/php
<?php
	require_once __DIR__."/../lib/rabbitMQLib.inc";
	require_once  __DIR__."/logger.php";

	$weblogger=new rabbitMQServer('RabbitMQ.ini','adminServer'); //Rename it to the actual name.

	echo "LOGGER LISTENING \n";
	
	$weblogger->process_requests('requestLogger');

?>
