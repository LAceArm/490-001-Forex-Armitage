#!/usr/bin/php
<?php
	require_once __DIR__."/lib/rabbitMQLib.inc";
	require_once __DIR__."/request_processor.php";

	$server=new rabbitMQServer('Email.ini','adminServer');

	echo "EMAIL IS NOW LISTENING".PHP_EOL;

	$server->process_requests('requestProcessor');
?>

