#!/usr/bin/php

<?php
	require_once("SendEmail.php");
	function requestProcessor($request){
		if(!isset($request["emails"])){
			echo "Request does not have emails".PHP_EOL;
			return ["success"=>false,"message"=>"No Emails in request"];
		}
		else if (!is_array($request["emails"])){
			echo "Request is not in a compatible type".PHP_EOL;
			return ["success"=>false,"message"=>"Incompatible type for request"];	
		}

		return Notification($request["emails"]);
	}
?>
