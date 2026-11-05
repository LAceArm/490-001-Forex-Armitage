<?php
require_once __DIR__ . "/../database.php";

function registerUser($username, $email, $password, $firstName = null, $lastName = null)
{
	global $conn;

	error_log(date("Y/m/d H:i:s").": REGISTRATION ATTEMPT".PHP_EOL, 3, "/home/seif490/WebsiteLogs/Registration.log");

	// Hash password for security
	$passwordHash = password_hash($password, PASSWORD_DEFAULT);

	// Add new user to users table
	$stmt = $conn->prepare(
		"INSERT INTO users
		(username, email, password_hash, first_name, last_name)
		VALUES (?, ?, ?, ?, ?)"
	);
	$stmt->bind_param(
		"sssss",
		$username,
		$email,
		$passwordHash,
		$firstName,
		$lastName
	);

	// Basic error catching (i.e., duplicate username or email); prevents listener crash
	try {
		$registered = $stmt->execute();
	} catch (Exception $e) {
		$registered = false;
	}

	// Runs if no issues above
	if ($registered) {
		$newUserId = $conn->insert_id; // ID of new user

		// Creates initial portfolio ($100,000.00 starting cash)
		$portfolioStmt = $conn->prepare(
			"INSERT INTO portfolios (user_id)
			VALUES (?)"
		);
		$portfolioStmt->bind_param("i", $newUserId);
		$portfolioStmt->execute();

		// Copies initial portfolio cash into USD holdings
		$positionStmt = $conn->prepare(
			"INSERT INTO positions (user_id, currency_id, amount)
			SELECT p.user_id, c.currency_id, p.starting_cash
			FROM portfolios p
			JOIN currencies c ON c.currency_code = 'USD'
			WHERE p.user_id = ?"
		);
		$positionStmt->bind_param("i", $newUserId);
		$positionStmt->execute();

		// User successfully registered
		error_log(date("Y/m/d H:i:s").": REGISTRATION WAS SUCCESSFUL".PHP_EOL, 3, "/home/seif490/WebsiteLogs/Registration.log");
		
		return [
			"success" => true,
			"user_id" => $newUserId
		];
	}

	// Reaches if user failed to register
	error_log(date("Y/m/d H:i:s").": REGISTRATION ATTEMPT WAS UNSUCCESSFUL".PHP_EOL, 3, "/home/seif490/WebsiteLogs/Registration.log");

	return [
		"success" => false,
		"error" => "Registration failed!"
	];
}
?>
