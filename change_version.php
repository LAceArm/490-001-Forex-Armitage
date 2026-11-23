#!/usr/bin/php 
<?php
	require_once __DIR__."/db/lib/rabbitMQLib.inc"; #Not a permanent solution
	function request_update($directory){
		$requestor=new rabbitMQClient("SCPFoundation.ini","Foundation");
		
		$request=array("System Name"=>$name);

		$response=$requestor->send_request($request);

		if(!$response["success"){ 
			return false;
		}
		return $response["File Path"];
	}


	function revert_previous($system){ 
		$requestor=new rabbitMQClient("SCPFoundation.ini","Foundation");

		$request=array("System Name" => $system);

		$response=$requestor->send_request($request);

		if(!$response["success"]){ 
			return false;
		}
		return $response["File Path"];
	}
?>
