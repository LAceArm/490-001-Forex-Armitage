<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../processors/mail_processor.php';

function sendUserEmail($userId, $username)
{
    global $conn;

    $stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        return ["success" => false, "error" => "No email on file"];
    }

    return SendEmail(["emails" => [$row["email"] => $username]]);
}