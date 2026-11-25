<?php
	require_once __DIR__ . "/lib/rabbitMQLib.inc";
	require_once __DIR__ . "/mail_processor.php";

	chdir(__DIR__ . '/config');

	$server = new rabbitMQServer('email_mq.ini','email');

	echo "EMAIL IS NOW LISTENING" . PHP_EOL;

	$server->process_requests('SendEmail');
?>

