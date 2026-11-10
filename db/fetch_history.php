<?php

require_once "database.php";

$symbols = [
    "USD/JPY",
    "USD/GBP",
    "USD/EUR",
    "USD/CNY",
    "USD/CHF",
    "USD/AED"
];

function getCurrencyId($conn, $code)
{	
    error_log(date("Y/m/d H:i:s").": GETTING CURRENCY ID".PHP_EOL,3,"/home/seif490/WebsiteLogs/Fetch.log"); 
    $stmt = $conn->prepare(
        "SELECT currency_id
         FROM currencies
         WHERE currency_code = ?"
    );

    $stmt->bind_param("s", $code);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();
    if(!$result){
	    error_log(date("Y/m/d H:i:s").": COULD NOT RESOLVE QUERY".PHP_EOL,3,"/home/seif490/WebsiteLogs/Fetch.log");
	   return ["Message"=> "Query could not be executed"]; 
    }
    error_log(date("Y/m/d H:i:s").": QUERY RESOLVED SUCCESSFULLY".PHP_EOL,3,"/home/seif490/WebsiteLogs/Fetch.log"); 
    return $result["currency_id"];
}

//laa55 11/10/26:
//I will add logging to this stuff once it's in the DMZ VM. Anything where there is an insertion is fair game though
//
//Update, same day: Screw it adding it now. It can be copied later anyways

$endDate = date("Y-m-d");
$startDate = date("Y-m-d", strtotime("-1 year"));

foreach ($symbols as $symbol) {

    [$baseCode, $quoteCode] = explode("/", $symbol);

    $url = "https://api.frankfurter.dev/v2/rates"
         . "?base=" . strtolower($baseCode)
         . "&quotes=" . strtolower($quoteCode)
         . "&from=" . $startDate
         . "&to=" . $endDate;

    $response = file_get_contents($url);

    if ($response === false) {    
        error_log(date("Y/m/d H:i:s").": FAILED TO FETCH HISTORY FOR $symbol".PHP_EOL,3,"/home/seif490/WebsiteLogs/Requests.log"); //Note: Be ready to update this value 
        echo "Failed to fetch history for $symbol" . PHP_EOL;
        continue;
    }
    else{ 
	    error_log(date("Y/m/d H:i:s").": HISTORY FOR $symbol FETCHED SUCCESSFULLY".PHP_EOL,3,"/home/seif490/WebsiteLogs/Requests.log"); 
	    //ditto
    }

    $data = json_decode($response, true);

    $baseId = getCurrencyId($conn, $baseCode);
    $quoteId = getCurrencyId($conn, $quoteCode);

    foreach ($data as $row) {

        if (!isset($row["date"]) || !isset($row["rate"])) {
            continue;
        }

        $rateDate = $row["date"];
        $rate = $row["rate"];
	
        error_log(date("Y/m/d H:i:s").": INSERTING CURRENCY INFORMATION INTO DATABASE".PHP_EOL,3,"/home/seif490/WebsiteLogs/Fetch.log"); 
	
	$stmt = $conn->prepare(
            "INSERT INTO rate_history
            (base_currency_id, quote_currency_id, base_currency_code, quote_currency_code, rate, rate_date)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
		rate = VALUES(rate),
		base_currency_code = VALUES(base_currency_code),
    		quote_currency_code = VALUES(quote_currency_code)"
        );

        $stmt->bind_param(
    	"iissds",
    	$baseId,
    	$quoteId,
    	$baseCode,
    	$quoteCode,
    	$rate,
    	$rateDate
	);

	$result=$stmt->execute();
	if($result){
            error_log(date("Y/m/d H:i:s").": QUERY EXECUTED SUCCESSFULLY".PHP_EOL,3,"/home/seif490/WebsiteLogs/Fetch.log"); 
	}
	else{ 
        	error_log(date("Y/m/d H:i:s").": COULD NOT INSERT CURRENCY INFORMATION".PHP_EOL,3,"/home/seif490/WebsiteLogs/Session.log"); 
	}
    }

    echo "$symbol history saved" . PHP_EOL;
}

?>
