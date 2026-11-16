#!/usr/bin/php
<?php 
	/*This file will handle sending Emails through gmail. PHPMailer (which will be used for push notifications going forward) is flexible,
	 but I'm using the email format that I use in my day-to-day life */
	
	//Load Configurations for PHPMailer
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
//	use Dotenv\Dotenv;
	require_once 'vendor/autoload.php';

	$env=@parse_ini_file(".env");

	$host=$env["SMTP_HOST"];
	$port=$env["SMTP_PORT"];
	$from=$env["EMAIL"];
	$password=$env["PASSWORD"];

	$mail=new PHPMailer();
	$mail->Host=$host;
	$mail->Port=$port;
	
	$mail->isSMTP();
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	$mail->SMTPAuth =true;

	$mail->Username=$from;
	$mail->Password=$password;

	$mail->setFrom($from,"NotifBot");
	$mail->addReplyTo($from,"NotifBot");
	$mail->addAddress("briantheduck259@gmail.com","Luis I");

	$mail->Subject="Test";
	$mail->isHTML(false);

	$mail->Body="This is a test";

	$mail->Send();


?>
