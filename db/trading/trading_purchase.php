<?php

function buyCurrency($userId, $currencyId, $amount)
{
 global $conn;

 $stmt = $conn->prepare(
  "INSERT INTO positions (user_id, currency_id, amount)
  VALUES (?, ?, ?)
  ON DUPLICATE KEY UPDATE amount=amount+VALUES(amount)"
 );

 $stmt->bind_param("iid", $userId, $currencyId, $amount);
 $stmt->execute();

 return true;
}
?>
