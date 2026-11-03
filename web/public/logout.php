<?php
session_start();

require_once __DIR__ . '/../broker/broker_client.php';

// Tell DB to delete session key
if (!empty($_SESSION['session_key'])) {
        send_to_broker([
                'type'        => 'logout',
                'session_key' => $_SESSION['session_key'],
        ]);
}

// Clear session, end it
$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;
?>
