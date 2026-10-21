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
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
	    
	//echo date("Y/m/d H:i:s")."LOGIN ATTEMPT WAS UNSUCCESSFUL: USER DOES NOT EXIST.".PHP_EOL >~/WebsiteLogs/Login.log;
        return ["success" => false];
    }

    $user = $result->fetch_assoc();

    if (!$user["is_active"]) {
        return ["success" => false];
    }

    if (!password_verify($password, $user["password_hash"])) {

	//echo date("Y/m/d H:i:s")."LOGIN ATTEMPT WAS UNSUCCESSFUL: PASSWORD DOES NOT MATCH.".PHP_EOL >~/WebsiteLogs/Login.log;
        return ["success" => false];
    }

    //echo date("Y/m/d H:i:s")."LOGIN ATTEMPT WAS SUCCESSFUL".PHP_EOL > ~/WebsiteLogs/Login.log;
    return [
        "success" => true,
        "user_id" => $user["user_id"],
        "username" => $user["username"],
        "role" => $user["role"]
    ];
}
?>
