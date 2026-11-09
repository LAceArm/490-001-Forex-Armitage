#!/usr/bin/php
<?php 
	/*This file will handle sending Emails through gmail. PHPMailer (which will be used for push notifications going forward) is flexible,
	 but I'm using the email format that I use in my day-to-day life */
	
	//Load Configurations for PHPMailer
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	require_once 'vendor/autoload.php';
	
	//Load .env details
	$env=parse_ini_file('.env');
	$password=$env["PASSWORD"];
	$email=$env["EMAIL"];

	//Create instance
	$mailman=new PHPMailer();

	//Configure new instance
	$mailman->isSMTP();
	$mailman->SMTPDebug=SMTP::DEBUG_SERVER;
	$mailman->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
	$mailman->Host = "smtp.gmail.com"; 
	$mailman->Username=$email;
	$mailman->Password=$password;
	$mailman->Port=465;

	$mailman->setFrom($email,"NotifBot");

	$mailman->addAddress('briantheduck259@gmail.com', 'Luis l');

	$mailman->isHTML(false);
	$mailman->Subject='Test email';
	$mailman->Body="This is a test email";

	$mailman->send();
?>
