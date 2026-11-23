#!/usr/bin/php 
<?php
	require_once __DIR__."/lib/rabbitMQLib.inc";
	function request_update($directory,$name,$address,$directory,$username){
		$requestor=new rabbitMQClient("SCPFoundation.ini","Foundation");
		
		$request=array("System Name"=>$name,"Username"=>$username,"Directory"=>$directory,"Address"=>$address);

		$response=$requestor->send_request($request);

		if(!$response["success"){ 
			return false;
		}
		return true;
	}
?> 
