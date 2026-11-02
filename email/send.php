#!/usr/bin/php
<?php 
require_once("SendEmail.php");

$emails=array("laa55@njit.edu");

$response=Notification($emails);

var_dump($response);
?>
