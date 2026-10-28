<?php
require_once __DIR__ . '/broker_client.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
}

if (empty($_SESSION['session_key'])) {
        header('Location: login.php');
        exit;
}

$reply = send_to_broker([
        'type' => 'validate_session',
        'session_key' => $_SESSION['session_key'] ?? ''
]);

if (empty($reply['success'])) {
        session_destroy();
        header('Location: login.php');
        exit;
}

// Validated user info for the page to use: user_id, username, role
$currentUser = $reply;
