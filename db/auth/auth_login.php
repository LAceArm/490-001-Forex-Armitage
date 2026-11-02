<?php

require_once __DIR__ . "/../database.php";
require_once "auth_session.php";

function loginUser($username, $password)
{
 global $conn;

 error_log(
  date("Y/m/d H:i:s").": LOGIN ATTEMPT FROM $username".PHP_EOL,
  3,
  "/home/seif490/WebsiteLogs/Login.log"
 );

 $stmt = $conn->prepare(
  "SELECT user_id, username, password_hash, role, is_active
  FROM users
  WHERE username = ?"
 );

 $stmt->bind_param("s", $username);

 try {

  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows !== 1) {

   error_log(
    date("Y/m/d H:i:s").": LOGIN ATTEMPT WAS UNSUCCESSFUL; USER $username DOES NOT EXIST".PHP_EOL,
    3,
    "/home/seif490/WebsiteLogs/Login.log"
   );

   return ["success" => false];
  }

  $user = $result->fetch_assoc();

  if (!$user["is_active"]) {

   error_log(
    date("Y/m/d H:i:s").": LOGIN ATTEMPT WAS UNSUCCESSFUL; USER $username IS INACTIVE".PHP_EOL,
    3,
    "/home/seif490/WebsiteLogs/Login.log"
   );

   return ["success" => false];
  }

  if (!password_verify($password, $user["password_hash"])) {

   error_log(
    date("Y/m/d H:i:s").": LOGIN ATTEMPT WAS UNSUCCESSFUL; PASSWORD MISMATCH".PHP_EOL,
    3,
    "/home/seif490/WebsiteLogs/Login.log"
   );

   return ["success" => false];
  }

  error_log(
   date("Y/m/d H:i:s").": LOGIN ATTEMPT WAS SUCCESSFUL".PHP_EOL,
   3,
   "/home/seif490/WebsiteLogs/Login.log"
  );

  return [
   "success" => true,
   "user_id" => $user["user_id"],
   "username" => $user["username"],
   "role" => $user["role"],
   "session_key" => createSession($user["user_id"])
  ];

 } catch (Exception $e) {

  error_log(
   date("Y/m/d H:i:s").": LOGIN ATTEMPT FAILED: ".$e->getMessage().PHP_EOL,
   3,
   "/home/seif490/WebsiteLogs/Login.log"
  );

  return ["success" => false];
 }

}

?>
