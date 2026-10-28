<?php

require_once "database.php";

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
         WHERE s.session_key = ? AND s.expires_at > NOW()"
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

    $stmt = $conn->prepare("DELETE FROM sessions WHERE session_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();

    return ["success" => true];
}

function registerUser($username, $email, $password, $firstName = null, $lastName = null)
{
    error_log(date("Y/m/d H:i:s").": REGISTRATION ATTEMPT".PHP_EOL,3,"/home/seif490/WebsiteLogs/Registration.log");
    global $conn;

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

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

    if ($stmt->execute()) {
	$newUserId = $conn->insert_id;
	$pstmt = $conn->prepare("INSERT INTO portfolios (user_id) VALUES (?)");
	$pstmt->bind_param("i", $newUserId);
	$pstmt->execute();
	
	$ustmt = $conn->prepare(
		"INSERT INTO positions (user_id, currency_id, amount)
		SELECT p.user_id, c.currency_id, p.starting_cash
		FROM portfolios p
		JOIN currencies c ON c.currency_code = 'USD'
		WHERE p.user_id = ?"
	);
	$ustmt->bind_param("i", $newUserId);
	$ustmt->execute();

	error_log(date("Y/m/d H:i:s").": REGISTRATION WAS SUCCESSFUL".PHP_EOL, 3, "/home/seif490/WebsiteLogs/Registration.log");
        return [
            "success" => true,
            "user_id" => $conn->insert_id
        ];
    }
    error_log(date("Y/m/d H:i:s").": REGISTRATION ATTEMPT WAS UNSUCCESSFUL".PHP_EOL,3,"/home/seif490/WebsiteLogs/Registration.log");
    return [
        "success" => false,
        "error" => $stmt->error
    ];
}

function loginUser($username, $password)
{	
	error_log(date("Y/m/d H:i:s").": LOGIN ATTEMPT FROM $username".PHP_EOL,3,"/home/seif490/WebsiteLogs/Login.log");
	 global $conn;

    $stmt = $conn->prepare(
        "SELECT user_id, username, password_hash, role, is_active
         FROM users
         WHERE username = ?"
    );

	 $stmt->bind_param("s", $username);

    try{
	    $stmt->execute();

	    $result= $stmt->get_result();

	    if($result->num_rows !== 1){ 
error_log(date("Y/m/d H:i:s").": LOGIN ATTEMPT WAS UNSUCCESSFUL; USER $username DOES NOT EXIST".PHP_EOL,3,"/home/seif490/WebsiteLogs/Login.log");
		    return ["success"=>false];
	    }

	    $user = $result->fetch_assoc();

	    if(!$user["is_active"]){ 
	error_log(date("Y/m/d H:i:s").": LOGIN ATTEMPT WAS UNSUCCESSUL; USER $username IS INACTIVE".PHP_EOL,3,"/home/seif490/WebsiteLogs/Login.log");
		    return ["success"=>false];
	    }

	    if(!password_verify($password,$user["password_hash"])){ 
		  error_log(date("Y/m/d H:i:s").": LOGIN ATTEMPT WAS UNSUCCESSUL; PASSWORD MISMATCH".PHP_EOL,3,"/home/seif490/WebsiteLogs/Login.log");
		    	return["success"=>false];
	    }
	    error_log(date("Y/m/d H:i:s").": LOGIN ATTEMPT WAS SUCCESSFUL".PHP_EOL,3,"/home/seif490/WebsiteLogs/Login.log");
	    return ["success"=>true,"user_id"=>$user["user_id"],"username"=>$user["username"],"role"=>$user["role"], "session_key"=>createSession($user["user_id"])];

    }

	catch (Exception $e){ 
	error_log(date("Y/m/d H:i:s").": LOGIN ATTEMPT WAS UNSUCCESSFUL; AN EXCEPTION OCCURED: ".$e->getMessage().PHP_EOL,3,"/home/seif490/WebsiteLogs/Login.log");	
	    return array("success"=>false);
    }
}

?>
