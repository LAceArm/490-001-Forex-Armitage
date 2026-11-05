#!/usr/bin/php
<?php 
	/*This file will handle sending Emails through gmail. PHPMailer (which will be used for push notifications going forward) is flexible,
	 but I'm using the email format that I use in my day-to-day life */
	
	//Load Configurations for PHPMailer
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use Dotenv/Dotenv;
	require_once 'vendor/autoload.php';
	
	//Load .env details
	$env=parse_ini_file('.env');
	$username=$env["USERNAME"];
	$password=$env["PASSWORD"];

	//Create instance
	$mailman=new PHPMailer();

	//Configure new instance
	$mailman->isSMTP();
	$mailman->SMTPDebug=SMTP::DEBUG_SERVER;
	$mailman->Host = "smtp.gmail.com"; //This is what will allow PHPMailer to send emails over gmail.
	$mailman->Port=465; //465 is a secure port since it sends information over TLS.
	$mailman->SMTPSecure=PHPMailer::ENCRYPTION_SMTPS; //Sets encryption algorithm
	$mailman->SMTPAuth = true; //Use SMTP authentication

	//Configure username and password
	$mailman->Username=$username;
	$mailman->Password=$password;

	//Configure Email
	$mailman->setFrom($username,'Luis Acevedo'); //Same as Username
	$mailman->addAddress('briantheduck259@gmail.com','Brian the Duck'); //Loop? 
	$mailman->Subject="PHPMailer GMail Test";
	//Can use HTML emails
	$mailman->msgHTML();

	$success= $mailman->send();
?>
