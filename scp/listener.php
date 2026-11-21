#!/usr/bin/php
<?php
	require_once __DIR__."/lib/rabbitMQLib.inc";
	require_once __DIR__."/request_processor.php";
	
	$server=new rabbitMQServer("SCPFoundation.ini","Foundation");
	
	echo "SCP SERVER IS NOW LISTENING FOR REQUESTS\n";
	$server->process_requests("scp_requestor");
?>
