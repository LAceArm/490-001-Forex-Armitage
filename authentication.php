<?php

require_once "database.php";

function registerUser($username, $email, $password, $firstName = null, $lastName = null)
{
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
        return [
            "success" => true,
            "user_id" => $conn->insert_id
        ];
    }

    return [
        "success" => false,
        "error" => $stmt->error
    ];
}

function loginUser($username, $password)
{
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
        return ["success" => false];
    }

    $user = $result->fetch_assoc();

    if (!$user["is_active"]) {
        return ["success" => false];
    }

    if (!password_verify($password, $user["password_hash"])) {
        return ["success" => false];
    }

    return [
        "success" => true,
        "user_id" => $user["user_id"],
        "username" => $user["username"],
        "role" => $user["role"]
    ];
}
?>
