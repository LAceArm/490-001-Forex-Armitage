<?php

require_once __DIR__ . "/database.php";
require_once __DIR__ . "/trading/trading_rates.php";
require_once __DIR__ . "/trading/trading_sales.php";
require_once __DIR__ . "/trading/trading_purchase.php";
require_once __DIR__ . "/trading/trading_history.php";

function executeTrade($userId, $fromCode, $toCode, $amount)
{
 global $conn;

 $fromCode = strtoupper(trim($fromCode));
 $toCode = strtoupper(trim($toCode));
 $amount = (float)$amount;

 if ($amount <= 0)
  return ["success"=>false, "error"=>"Invalid amount"];

 if ($fromCode == $toCode)
  return ["success"=>false, "error"=>"Choose different currencies"];

 $from = currencyInfo($fromCode);
 $to = currencyInfo($toCode);

 if (!$from || !$to)
  return ["success"=>false, "error"=>"Currency not found"];

 $toAmount = $amount / $from["rate"] * $to["rate"];
 $rate = $toAmount / $amount;

 try {

  $conn->begin_transaction();

  if (!sellCurrency($userId, $from["id"], $amount)) {
   $conn->rollback();
   return ["success"=>false, "error"=>"Insufficient balance"];
  }

  buyCurrency($userId, $to["id"], $toAmount);

  saveTrade(
   $userId,
   $from["id"],
   $to["id"],
   $amount,
   $toAmount,
   $rate
  );

  $conn->commit();

 } catch (Exception $e) {
  $conn->rollback();
  return ["success"=>false, "error"=>"Trade failed"];
 }

 return [
  "success"=>true,
  "from"=>$fromCode,
  "to"=>$toCode,
  "from_amount"=>$amount,
  "to_amount"=>$toAmount,
  "rate"=>$rate
 ];
}
?>
