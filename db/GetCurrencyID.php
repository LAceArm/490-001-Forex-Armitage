#!/usr/bin/php
<?php 
	require_once "database.php";
	function getCurrencyId($conn, $code)
{
   
    error_log(date("Y/m/d H:i:s").": GETTING CURRENCY IDs".PHP_EOL,3,"/home/seif490/WebsiteLogs/Fetch.log"); 
    $stmt = $conn->prepare(
        "INSERT INTO currencies (currency_code, currency_name)
         VALUES (?, ?)
         ON DUPLICATE KEY UPDATE currency_code = currency_code"
    );

    $stmt->bind_param("ss", $code, $code);
    $stmt->execute();

    $stmt = $conn->prepare(
        "SELECT currency_id
         FROM currencies
         WHERE currency_code = ?"
    );

    $stmt->bind_param("s", $code);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();
    if(!$result){
	    error_log(date("Y/m/d H:i:s").": COULD NOT GET CURRENCY".PHP_EOL,3,"/home/seif490/WebsiteLogs/Fetch.log"); 
	    return ["FAILED TO GET CURRENCIES"];
    }
    error_log(date("Y/m/d H:i:s").": SUCCESSFULLY FETCHED CURRENCY $code".PHP_EOL,3,"/home/seif490/WebsiteLogs/Fetch.log"); 
    return $result["currency_id"];
}

?>
