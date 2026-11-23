#!/usr/bin/php
<?php
	require_once __DIR__."/db/lib/rabbitMQLib.inc" #still not a permanent solution
	function revert_previous($system,$address,$directory,$username){ 
		$requestor=new rabbitMQClient("SCPFoundation.ini","Foundation");

		$request=array("System Name" => $system, "Address"=>$address,"Directory"=>$directory,"Username"=>$username);

		$response=$requestor->send_request($request);

		if(!$response["success"]){ 
			return false;
		}
		return true;
	}
?>
