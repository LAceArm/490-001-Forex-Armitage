#!/usr/bin/php
<?php 
	/*This file will handle sending Emails through gmail. PHPMailer (which will be used for push notifications going forward) is flexible,
	 but I'm using the email format that I use in my day-to-day life */
	
	//Load Configurations for PHPMailer
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	require_once 'vendor/autoload.php';

	//Create instance
	$mailman=new PHPMailer();

	//Configure new instance
	$mailman->isSMTP();
	$mailman->SMTPDebug=SMTP::DEBUG_SERVER;
	$mailman->Host = "smtp.gmail.com"; //This is what will allow PHPMailer to send emails over gmail.
	$mailman->Port=465; //465 is a secure port since it sends information over TLS.
?>
