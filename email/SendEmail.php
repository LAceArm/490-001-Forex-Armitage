#!/usr/bin/php
<?php
	require __DIR__.'/vendor/autoload.php';
	$env=parse_ini_file('.env');
	$key=$env["API_KEY"];

	function Notification($emails=array()){ 
		global $key;
		$resend=Resend::client($key);

		$resend->emails->send([
  			'from' => 'Acme <onboarding@resend.dev>',
  			'to' => ['delivered@resend.dev'],
  			'subject' => 'hello world',
  			'html' => '<strong>it works!</strong>',
			]);
		
	}
?>
