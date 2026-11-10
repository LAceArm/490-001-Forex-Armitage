#!/usr/bin/php 
<?php
	function requestLogger($message){ 
		$decode=json_decode($message,true);

		if(!isset($decode["type"])){ 
			error_log(date("Y/m/d H:i:s").": EVENT NOT LOGGED: EVENT TYPE NOT SET".PHP_EOL,3,"/home/chris490/WebSiteLogs/logger.log");
			return;
		}
		$msg=$decode["message"];
		switch($decode["type"]){
			case "login":
				error_log($msg,3,"/home/chris490/WebSiteLogs/DatabaseLogs/Login.log");
				break;
			case "validate":
				error_log($msg,3,"/home/chris490/WebsiteLogs/DatabaseLogs/Validate.log");
				break;
			case "register":
				error_log($msg,3,"/home/chris490/WebsiteLogs/DatabaseLogs/Register.log");
				break;
			case "currency":
				error_log($msg,3,"/home/chris490/WebsiteLogs/DatabaseLogs/Fetch.log");
				break;
			case "trade":
				error_log($msg,3,"/home/chris490/WebsiteLogs/DatabaseLogs/Trade.log");
				break;
			case "watchlist":
				error_log($msg,3,"/home/chris490/WebsiteLogs/DatabaseLogs/Watchlist.log");
				break;
			case "rates":
				error_log($msg,3,"/home/chris490/WebsiteLogs/DatabaseLogs/Rates.log");
				break;
			case "portfolio":
				error_log($msg,3,"/home/chris490/WebsiteLogs/DatabaseLogs/Portfolio.log");
				break;
		}
	}
?>
