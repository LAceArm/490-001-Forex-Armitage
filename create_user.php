<?php

require_once "auth.php";

if ($argc < 3) {
    echo "Usage: php create_user.php email password\n";
    exit();
}

$email = $argv[1];
$password = $argv[2];

$username = $email;

$result = registerUser(
    $username,
    $email,
    $password
);

print_r($result);

?>
