<?php

require_once __DIR__ . "/../database.php";

function createSession($userId)
{
 global $conn;

 $key = bin2hex(random_bytes(32));

 $stmt = $conn->prepare(
  "INSERT INTO sessions (session_key, user_id, created_at, expires_at)
  VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 HOUR))"
 );

 $stmt->bind_param("si", $key, $userId);
 $stmt->execute();

 return $key;
}

function validateSession($key)
{
 global $conn;

 $stmt = $conn->prepare(
  "SELECT u.user_id, u.username, u.role
  FROM sessions s
  JOIN users u ON u.user_id = s.user_id
  WHERE s.session_key = ?
  AND s.expires_at > NOW()"
 );

 $stmt->bind_param("s", $key);
 $stmt->execute();

 $row = $stmt->get_result()->fetch_assoc();

 if (!$row) {
  return ["success" => false];
 }

 return [
  "success" => true,
  "user_id" => $row["user_id"],
  "username" => $row["username"],
  "role" => $row["role"]
 ];
}

function destroySession($key)
{
 global $conn;

 $stmt = $conn->prepare(
  "DELETE FROM sessions WHERE session_key = ?"
 );

 $stmt->bind_param("s", $key);
 $stmt->execute();

 return ["success" => true];
}

?>
