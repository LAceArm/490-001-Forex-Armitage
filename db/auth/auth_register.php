<?php

require_once __DIR__ . "/../database.php";

function registerUser($username, $email, $password, $firstName = null, $lastName = null)
{
 global $conn;

 error_log(
  date("Y/m/d H:i:s").": REGISTRATION ATTEMPT".PHP_EOL,
  3,
  "/home/seif490/WebsiteLogs/Registration.log"
 );

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

  $pstmt = $conn->prepare(
   "INSERT INTO portfolios (user_id)
   VALUES (?)"
  );

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

  error_log(
   date("Y/m/d H:i:s").": REGISTRATION WAS SUCCESSFUL".PHP_EOL,
   3,
   "/home/seif490/WebsiteLogs/Registration.log"
  );

  return [
   "success" => true,
   "user_id" => $newUserId
  ];
 }

 error_log(
  date("Y/m/d H:i:s").": REGISTRATION ATTEMPT WAS UNSUCCESSFUL".PHP_EOL,
  3,
  "/home/seif490/WebsiteLogs/Registration.log"
 );

 return [
  "success" => false,
  "error" => $stmt->error
 ];
}

?>
