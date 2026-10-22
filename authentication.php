<?php

require_once "database.php";

function registerUser($username, $email, $password, $firstName = null, $lastName = null)
{
    //error_log(date("Y/m/d H:i:s").": Registration Attempt
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
	 //   error_log(date("Y/m/d H:i:s").": Registration success".PHP_EOL, 3, "~/WebsiteLogs/Registration.log");
        return [
            "success" => true,
            "user_id" => $conn->insert_id
        ];
    }
	//echo date("Y/m/d H:i:s")."REGISTRATION ATTEMPT WAS UNSUCCESSFUL".PHP_EOL >~/WebsiteLogs/Registration.log;
    return [
        "success" => false,
        "error" => $stmt->error
    ];
}

function loginUser($username, $password)
{	
	
	//echo date("Y/m/d H:i:s")."LOGIN ATTEMPT FROM $username".PHP_EOL >~/WebsiteLogs/Login.log;
	 global $conn;

    $stmt = $conn->prepare(
        "SELECT user_id, username, password_hash, role, is_active
         FROM users
         WHERE username = ?"
    );

    $stmt->bind_param("s", $username);
    try {
	$stmt->execute();
    } catch (mysqli_sql_exception $e) {
	if ($e->getCode() === 1062) {
	    $field = str_contains($e->getMessage(), 'email') ? "Email" : "Username";
    #        error_log(date("Y/m/d H:i:s") . " REGISTRATION ATTEMPT WAS UNSUCCESSFUL: DUPLICATE " . strtoupper($field) . PHP_EOL, 3, "/home/seif490/WebsiteLogs/Registration.log");
            return ["success" => false, "error" => "$field already exists"];
	}

   #     error_log(date("Y/m/d H:i:s") . " REGISTRATION ATTEMPT WAS UNSUCCESSFUL: " . $e->getMessage() . PHP_EOL, 3, "/home/seif490/WebsiteLogs/Registration.log");
        return ["success" => false, "error" => "Registration failed"];
    }

 //   error_log(date("Y/m/d H:i:s") . " REGISTRATION ATTEMPT WAS SUCCESSFUL" . PHP_EOL, 3, "/home/seif490/WebsiteLogs/Registration.log");
    return ["success" => true, "user_id" => $conn->insert_id];
}

?>
