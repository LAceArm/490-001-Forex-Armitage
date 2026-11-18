#!/usr/bin/php
<?php	
	require_once __DIR__."/lib/rabbitMQLib.inc"
	exec('tar -cvf backend_dev.tar.gz /home/jdk39/Project/490-001-octo-fishstick/db',$output,$canary);

	if($canary != 0){ 
		echo "SOMETHING WENT WRONG".PHP_EOL;
		return; 
	}

	exec("scp backend_dev.tar.gz laa55-scp-server@deploy:/home/laa55-scp-server/BackEndQADeploys",$output,$canary);

	if ($canary != 0){ 
		echo "COULD NOT SEND FILE".PHP_EOL;
		return;
	}

	//Publish Message to SCP telling it that a new Version of a file is available 
?>
