<?php
require_once __DIR__ . "/../database.php";
require_once __DIR__ . "/../clients/dmz_client.php";

$reply = sendToDmz(["type" => "get_current_rates"]);

if (!$reply["success"]) {
	echo "DMZ error: " . ($reply["error"] ?? "unknown") . "\n";
	exit(1);
}

foreach ($reply["rates"] as $entry) {
	$code = $entry["quote"];
	$rate = $entry["rate"];
	$date = $entry["date"];

	// Find the currency's id from its code
	$stmt = $conn->prepare("SELECT currency_id FROM currencies WHERE currency_code = ?");
	$stmt->bind_param("s", $code);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();

	if (!$row) {
		continue; // Currency not in our database, skip it
	}
	$currencyId = (int)$row["currency_id"];

	// Overwrite the latest rate
	$stmt = $conn->prepare("UPDATE rates SET units_per_usd = ? WHERE currency_id = ?");
	$stmt->bind_param("di", $rate, $currencyId);
	$stmt->execute();

	// Save today's history row, replacing it if the script already ran today
	$stmt = $conn->prepare("DELETE FROM rate_history WHERE currency_id = ? AND rate_date = ?");
	$stmt->bind_param("is", $currencyId, $date);
	$stmt->execute();

	$stmt = $conn->prepare("INSERT INTO rate_history (currency_id, units_per_usd, rate_date) VALUES (?, ?, ?)");
	$stmt->bind_param("ids", $currencyId, $rate, $date);
	$stmt->execute();
}

echo "Rates updated\n";
