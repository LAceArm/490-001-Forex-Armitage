<?php
$conn = new mysqli("127.0.0.1", "appuser", "localdev", "fxtrader");
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}
