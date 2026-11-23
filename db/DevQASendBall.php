#!/usr/bin/php
<?php
	/* 11/23/26
	 * This file will send a TAR ball to the Deploy Server. It uses Command Line arguments to send a Specific file over to the server
	 * using SCP. Unlike CreateBall, which is universal, this script is very specific. */ 
	
	if(!isset($argv[1])){ //[0] is the name of the file. [1] would be the directory to the tar ball
		echo "MISSING ARGUMENTS \n"
		exit;	
	}

	$path=$argv[1];

	exec("scp $path laa55-scp-server@deploy:/home/laa55-scp-server/Deploys/BackEndQualityAssurance/",$output,$canary);

	if($canary !== 0){ 
		echo "COULD NOT SEND FILE\n";
		exit;
	}

?>
