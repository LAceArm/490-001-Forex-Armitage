#!/usr/bin/php
<?php 
	require_once __DIR__."/mail.php";

	function requestProcessor($request) {
		$postman=getMail();

		$postman->addAddress("briantheduck259@gmail.com","Luis I");

		$postman->Subject="Important Update Regarding your investments";

		$postman->isHTML(false);$postman->Body="Good Evening. We are writing to inform you of a major shakeup in one of the currencies you follow. Please log in to view this matter";

		$postman->Send();
	       return array("Message" => "Currently nothing!");	
	}

	$request=array();

	requestProcessor($request);
?>
