#!/usr/bin/php
<?php 
	require_once "database.php";
$daystr=date("Y/m/d H:i:s").": ";

function getCurrencyId($conn, $code)
{	
	global $daystr;
   
 //   error_log(date("Y/m/d H:i:s").": GETTING CURRENCY IDs".PHP_EOL,3,"/home/seif490/WebsiteLogs/Fetch.log"); 
    $stmt = $conn->prepare(
        "INSERT INTO currencies (currency_code, currency_name)
         VALUES (?, ?)
         ON DUPLICATE KEY UPDATE currency_code = currency_code"
    );

    $stmt->bind_param("ss", $code, $code);
    $r1= $stmt->execute();

    if(!$r1){
	    $msg=$daystr."COULD NOT EXECUTE INSERTION QUERY".PHP_EOL;
	    return null; 
    }

    $stmt = $conn->prepare(
        "SELECT currency_id
         FROM currencies
         WHERE currency_code = ?"
    );

    $stmt->bind_param("s", $code);
    $r2=$stmt->execute();
    if(!$r2){ 
	    $msg=$daystr."COULD NOT EXECUTE QUERY: THERE WAS AN ERROR: ".$stmt->error.PHP_EOL;
	    error_log($msg,3,"/home/jdk39/WebsiteLogs/Fetch.log");
	    return "XXXXXXXXXX";
    }

    $result = $stmt->get_result()->fetch_assoc();
    if(!isset($result["currency_id"]){ 
	    $msg=$daystr."COULD NOT FIND CURRENCY ID".PHP_EOL;
	    error_log($msg,3,"/home/jdk39/WebsiteLogs/Fetch.log";
	    return null;
    }
    $msg=$daystr."FOUND CURRENCY ID".PHP_EOL;
    error_log($msg,3,"/home/jdk39/WebsiteLogs/Fetch.log";
    return $result["currency_id"];
}


?>
