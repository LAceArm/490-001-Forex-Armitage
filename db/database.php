<?php
$cfg = parse_ini_file(__DIR__ . '/config/backend_mysql.ini');
if ($cfg === false) {
    die("DB config missing: config/backend_mysql.ini" . PHP_EOL);
}

$conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['name']);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error . PHP_EOL);
}
