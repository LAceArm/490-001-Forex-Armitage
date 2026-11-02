<?php
// Connection to MySQL, shared by the listener and the cron scripts.
// Credentials come from config/db.ini (ignored by git), not from this file.

$cfg = parse_ini_file(__DIR__ . '/config/db.ini');
if ($cfg === false) {
    die("DB config missing: config/db.ini" . PHP_EOL);
}

$conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['name']);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error . PHP_EOL);
}

$conn->set_charset('utf8mb4');   // match the tables' character set
