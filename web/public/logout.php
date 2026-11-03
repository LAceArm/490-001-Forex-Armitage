<?php
session_start(); // Start session

require_once __DIR__ . '/../broker/broker_client.php'; // Loads send_to_broker()

// Bothers DB to delete this session's key
if (!empty($_SESSION['session_key'])) {
        send_to_broker([
                'type'        => 'logout',
                'session_key' => $_SESSION['session_key'],
        ]);
}

session_destroy(); // Stop session

header('Location: login.php'); // Redirect to login.php