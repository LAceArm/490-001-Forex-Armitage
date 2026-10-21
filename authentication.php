<?php

require_once "database.php";

function registerUser($username, $email, $password, $firstName = null, $lastName = null)
{
<<<<<<< HEAD
    //error_log(date("Y/m/d H:i:s").": Registration Attempt
=======
    error_log(date("Y/m/d H:i:s") . " REGISTER ATTEMPT FROM $username" . PHP_EOL, 3, "/home/seif490/WebsiteLogs/Registration.log");
>>>>>>> 0cb8bec (Handle duplicate username/email on registration)
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

<<<<<<< HEAD
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
=======
    try {
	$stmt->execute();
    } catch (mysqli_sql_exception $e) {
	if ($e->getCode() === 1062) {
	    $field = str_contains($e->getMessage(), 'email') ? "Email" : "Username";
            error_log(date("Y/m/d H:i:s") . " REGISTRATION ATTEMPT WAS UNSUCCESSFUL: DUPLICATE " . strtoupper($field) . PHP_EOL, 3, "/home/seif490/WebsiteLogs/Registration.log");
            return ["success" => false, "error" => "$field already exists"];
	}

        error_log(date("Y/m/d H:i:s") . " REGISTRATION ATTEMPT WAS UNSUCCESSFUL: " . $e->getMessage() . PHP_EOL, 3, "/home/seif490/WebsiteLogs/Registration.log");
        return ["success" => false, "error" => "Registration failed"];
    }

    error_log(date("Y/m/d H:i:s") . " REGISTRATION ATTEMPT WAS SUCCESSFUL" . PHP_EOL, 3, "/home/seif490/WebsiteLogs/Registration.log");
    return ["success" => true, "user_id" => $conn->insert_id];
}

function loginUser($username, $password)
{
    error_log(date("Y/m/d H:i:s") . " LOGIN ATTEMPT FROM $username" . PHP_EOL, 3, "/home/seif490/WebsiteLogs/Login.log");
>>>>>>> 0cb8bec (Handle duplicate username/email on registration)
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
<<<<<<< HEAD
	    
	//echo date("Y/m/d H:i:s")."LOGIN ATTEMPT WAS UNSUCCESSFUL: USER DOES NOT EXIST.".PHP_EOL >~/WebsiteLogs/Login.log;
=======
        error_log(date("Y/m/d H:i:s") . " LOGIN ATTEMPT WAS UNSUCCESSFUL: USER DOES NOT EXIST." . PHP_EOL, 3, "/home/seif490/WebsiteLogs/Login.log");
>>>>>>> 0cb8bec (Handle duplicate username/email on registration)
        return ["success" => false];
    }

    $user = $result->fetch_assoc();

    if (!$user["is_active"]) {
        error_log(date("Y/m/d H:i:s") . " LOGIN ATTEMPT WAS UNSUCCESSFUL: USER NOT ACTIVE." . PHP_EOL, 3, "/home/seif490/WebsiteLogs/Login.log");
        return ["success" => false];
    }

    if (!password_verify($password, $user["password_hash"])) {
<<<<<<< HEAD

	//echo date("Y/m/d H:i:s")."LOGIN ATTEMPT WAS UNSUCCESSFUL: PASSWORD DOES NOT MATCH.".PHP_EOL >~/WebsiteLogs/Login.log;
        return ["success" => false];
    }

    //echo date("Y/m/d H:i:s")."LOGIN ATTEMPT WAS SUCCESSFUL".PHP_EOL > ~/WebsiteLogs/Login.log;
=======
        error_log(date("Y/m/d H:i:s") . " LOGIN ATTEMPT WAS UNSUCCESSFUL: PASSWORD DOES NOT MATCH." . PHP_EOL, 3, "/home/seif490/WebsiteLogs/Login.log");
        return ["success" => false];
    }

    error_log(date("Y/m/d H:i:s") . " LOGIN ATTEMPT WAS SUCCESSFUL" . PHP_EOL, 3, "/home/seif490/WebsiteLogs/Login.log");
>>>>>>> 0cb8bec (Handle duplicate username/email on registration)
    return [
        "success" => true,
        "user_id" => $user["user_id"],
        "username" => $user["username"],
        "role" => $user["role"]
    ];
}
?>
