<?php
require_once __DIR__ . "/../database.php";
require_once "auth_session.php";

function loginUser($username, $password)
{
	global $conn;

	error_log(date("Y/m/d H:i:s").": LOGIN ATTEMPT FROM $username".PHP_EOL, 3, "/home/seif490/WebsiteLogs/Login.log");

	// Search user by username
	$stmt = $conn->prepare(
		"SELECT user_id, username, password_hash, role, is_active
		FROM users
		WHERE username = ?"
	);
	$stmt->bind_param("s", $username);
	$stmt->execute();

	// User's row (or nothing, if username doesn't exist)
	$user = $stmt->get_result()->fetch_assoc();

	if (!$user) {
		error_log(date("Y/m/d H:i:s").": LOGIN ATTEMPT WAS UNSUCCESSFUL; PASSWORD MISMATCH".PHP_EOL, 3, "/home/seif490/WebsiteLogs/Login.log");
		return ["success" => false];
	}
	
	// Compares typed password to stored hash
	if (!password_verify($password, $user["password_hash"])) {
		error_log(date("Y/m/d H:i:s").": LOGIN ATTEMPT WAS UNSUCCESSFUL; PASSWORD MISMATCH".PHP_EOL, 3, "/home/seif490/WebsiteLogs/Login.log");
		return ["success" => false];
	}

	error_log(date("Y/m/d H:i:s").": LOGIN ATTEMPT WAS SUCCESSFUL".PHP_EOL, 3, "/home/seif490/WebsiteLogs/Login.log");

	// Passes, createSession makes session key and saves it in database's session table
	return [
		"success" => true,
		"user_id" => $user["user_id"],	
		"username" => $user["username"],
		"role" => $user["role"],
		"session_key" => createSession($user["user_id"])
	];
}
?>
