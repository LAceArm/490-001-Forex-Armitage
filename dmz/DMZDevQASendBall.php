#!/usr/bin/php
<?php
	/* 11/23/26
	 * This file will send a TAR ball to the Deploy Server. It uses Command Line arguments to send a Specific file over to the server
	 * using SCP. Unlike CreateBall, which is universal, this script is very specific. */ 
	
	require_once __DIR__."/lib/rabbitMQLib.inc";
	
	if(!isset($argv[1])){ //[0] is the name of the file. [1] would be the directory to the tar ball
		echo "MISSING FILE NAME ARGUMENTS \n"
		exit;	
	}

	$path=$argv[1];

	if(!isset($argv[2])){ 
		echo "MISSING FILE NAME ARGUMENT \n";
		exit;
	}
	$name=$argv[2];

	exec("scp $path laa55-scp-server@deploy:/home/laa55-scp-server/Deploys/DMZQualityAssurance/",$output,$canary);

	if($canary !== 0){ 
		echo "COULD NOT SEND FILE\n";
		exit;
	}

	$client=new rabbitMQClient('SCPFoundation.ini','Foundation');
	
	$message=array("type"=>"New Update","System Name"=>"DMZQualityAssurance","Version Name"=>$name);
		
	$client->publish($message); // Does not expect a response at all  

?>
