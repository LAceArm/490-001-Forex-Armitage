<?php

$host = "127.0.0.1";
$db   = "it490_project";
$user = "it490app";
$pass = "Hotwheels2003!";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error . PHP_EOL);
}

?>
