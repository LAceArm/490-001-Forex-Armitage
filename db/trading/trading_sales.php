<?php

function sellCurrency($userId, $currencyId, $amount)
{
 global $conn;

 $stmt = $conn->prepare(
  "SELECT amount FROM positions
  WHERE user_id=? AND currency_id=? FOR UPDATE"
 );

 $stmt->bind_param("ii", $userId, $currencyId);
 $stmt->execute();

 $row = $stmt->get_result()->fetch_assoc();

 if (!$row || $row["amount"] < $amount) {
  return false;
 }

 $stmt = $conn->prepare(
  "UPDATE positions
  SET amount=amount-?
  WHERE user_id=? AND currency_id=?"
 );

 $stmt->bind_param("dii", $amount, $userId, $currencyId);
 $stmt->execute();

 return true;
}
?>
