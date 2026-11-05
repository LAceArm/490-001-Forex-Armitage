<?php

require_once __DIR__ . "/../database.php";

function createSession($userId)
{
	global $conn;

	$sessionKey = bin2hex(random_bytes(32)); // Create hash

	// Create session; establish with $sessionKey and $userId
	$stmt = $conn->prepare(
		"INSERT INTO sessions (session_key, user_id, created_at, expires_at)
		VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 HOUR))"
	);
	$stmt->bind_param("si", $sessionKey, $userId);
	$stmt->execute();

	return $sessionKey;
}

function validateSession($sessionKey)
{
	global $conn;

	// If key hasn't expired, locate associated user
	$stmt = $conn->prepare(
		"SELECT u.user_id, u.username
		FROM sessions s
		JOIN users u ON u.user_id = s.user_id
		WHERE s.session_key = ?
		AND s.expires_at > NOW()"
	);
	$stmt->bind_param("s", $sessionKey);
	$stmt->execute();

	// Retrieve user_id and username associated with key
	$row = $stmt->get_result()->fetch_assoc();

	// If key is unassociated
	if (!$row) {
		return ["success" => false];
	}

	// Otherwise...
	return [
	"success" => true,
	"user_id" => $row["user_id"],
	"username" => $row["username"],
	];
}

function destroySession($sessionKey)
{
	global $conn;

	// Delete sessions associated with key
	$stmt = $conn->prepare(
		"DELETE FROM sessions WHERE session_key = ?"
	);
	$stmt->bind_param("s", $sessionKey);
	$stmt->execute();

	return ["success" => true];
}
?>
