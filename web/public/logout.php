<?php
/*
 * logout.php  (Web VM)
 *
 * Ends the user's session and sends them back to the login page.
 * The "Logout" link in home.php points here.
 */

session_start();

error_log("LOGOUT key=" . ($_SESSION['session_key'] ?? 'NONE'));

require_once __DIR__ . '/../broker/broker_client.php';

// Tell the DB side to delete this session's key, so it can't be reused.
if (!empty($_SESSION['session_key'])) {
        send_to_broker([
                'type'        => 'logout',
                'session_key' => $_SESSION['session_key'],
        ]);
}

// Empty the session data for this request.
$_SESSION = [];

// Delete the session cookie in the browser, so the old session ID
// can't be reused. Skipped if PHP isn't using cookies for sessions.
if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(
                session_name(),
                '',
                time() - 42000,
                $p['path'],
                $p['domain'],
                $p['secure'],
                $p['httponly']
        );
}

// Destroy the session data stored on the server.
session_destroy();

header('Location: login.php');
exit;
