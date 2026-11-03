<?php
require_once __DIR__ . '/broker_client.php';

// If session doesn't exist, start it
if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
}

// If session key doesn't exist, nobody is signed in
if (empty($_SESSION['session_key'])) {
        header('Location: login.php');
        exit;
}

// Check if session key is valid
$reply = send_to_broker([
        'type' => 'validate_session',
        'session_key' => $_SESSION['session_key']
]);

// If invalid or unreachable, kill session and go back to login.php
if (empty($reply['success'])) {
        session_destroy();
        header('Location: login.php');
        exit;
}

// Validated user info (user_id, username)
$currentUser = $reply;
