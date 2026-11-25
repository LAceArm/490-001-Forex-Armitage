<?php
require_once __DIR__ . '/../communication/mail.php';

function SendEmail($request) {
	$postman = getMail();

	if (!isset($request["emails"])) {
		return array("success" => false, "message" => "No emails sent");
	}
	else if (gettype($request["emails"]) !== "array") {
			return array("success" => false, "message" => "Emails must be of type Array");	
	}
	else if (empty($request["emails"])) {
		return array("success" => false, "message" => "Email Array must not be empty");
	}

	// Note: the following loop assumes an associative array Where the Email is the key and the Username is the value
	$mailinglist = $request["emails"];

	foreach($mailinglist as $email => $username) {
		$postman->addAddress($email,$username);
	}

	$postman->Subject = "Important Update Regarding your investments";

	$postman->msgHTML(file_get_contents(__DIR__ . '/../communication/email.html'));

	if ($postman->Send()) {
			return array("message" => "Email Sent Successfully", "success" => true);
	}
	else {
			return array("message" => "Email could not be sent", "success" => false);
	}
}
