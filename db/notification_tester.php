#!/usr/php
<?php
	require_once __DIR__."/lib/rabbitMQLib.inc";

	$notifClient=new rabbitMQClient('RabbitMQ.ini','adminServer');

	$emails=array('laa55@njit.edu',"test@test.com","duo@uno.com");

	$request=array();
	$request["message"]="Testing notification stuff";
	$request["emails"]=$emails;
	$response=$notifClient->send_request($request);

	var_dump($response);
?>
