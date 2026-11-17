#!/usr/bin/php
<?php 
	require_once __DIR__."/mail.php";

	function requestProcessor($request){
		$postman=getMail();
	       return array("Message" => "Currently nothing!");	
	}
?>
