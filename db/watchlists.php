<?php

require_once "database.php";

function getWatchlists($userId)
{
 global $conn;

 $stmt = $conn->prepare(
  "SELECT w.watchlist_id, w.name, c.currency_id, c.currency_code
  FROM watchlists w
  LEFT JOIN watchlist_items i ON i.watchlist_id = w.watchlist_id
  LEFT JOIN currencies c ON c.currency_id = i.currency_id
  WHERE w.user_id = ?
  ORDER BY w.watchlist_id, c.currency_code"
 );

 $stmt->bind_param("i", $userId);
 $stmt->execute();

 $result = $stmt->get_result();

 $latest = [];

 $res = $conn->query(
  "SELECT r.quote_currency_id AS id, r.rate
  FROM rates r
  JOIN currencies b ON b.currency_id = r.base_currency_id
  WHERE b.currency_code = 'USD'"
 );

 while ($row = $res->fetch_assoc()) {
  $latest[$row["id"]] = (float)$row["rate"];
 }

 $previous = [];

 $res = $conn->query(
  "SELECT h.quote_currency_id AS id, h.rate
  FROM rate_history h
  JOIN (
   SELECT quote_currency_id, MAX(rate_date) AS d
   FROM rate_history
   WHERE base_currency_code = 'USD'
   AND rate_date < (SELECT MAX(rate_date) FROM rate_history)
   GROUP BY quote_currency_id
  ) p ON p.quote_currency_id = h.quote_currency_id
  AND p.d = h.rate_date
  WHERE h.base_currency_code = 'USD'"
 );

 while ($row = $res->fetch_assoc()) {
  $previous[$row["id"]] = (float)$row["rate"];
 }

 $lists = [];

 while ($row = $result->fetch_assoc()) {

  $wid = (int)$row["watchlist_id"];

  if (!isset($lists[$wid])) {
   $lists[$wid] = [
    "id" => $wid,
    "name" => $row["name"],
    "currencies" => []
   ];
  }

  if ($row["currency_id"] === null) {
   continue;
  }

  $cid = (int)$row["currency_id"];
  $rate = $latest[$cid] ?? null;
  $prev = $previous[$cid] ?? null;

  $price = $rate ? 1 / $rate : null;

  $change = ($rate && $prev)
   ? ((1 / $rate) / (1 / $prev) - 1) * 100
   : null;

  $lists[$wid]["currencies"][] = [
   "code" => $row["currency_code"],
   "price" => $price,
   "change" => $change
  ];
 }

 return [
  "success" => true,
  "watchlists" => array_values($lists)
 ];
}

function createWatchlist($userId, $name)
{
 global $conn;

 $name = trim($name);

 if ($name === "" || strlen($name) > 50) {
  return [
   "success" => false,
   "error" => "Enter a name up to 50 characters"
  ];
 }

 $stmt = $conn->prepare(
  "INSERT INTO watchlists (user_id, name)
  VALUES (?, ?)"
 );

 $stmt->bind_param("is", $userId, $name);
 $stmt->execute();

 return [
  "success" => true,
  "watchlist_id" => $conn->insert_id
 ];
}

function deleteWatchlist($userId, $watchlistId)
{
 global $conn;

 $stmt = $conn->prepare(
  "DELETE FROM watchlists
  WHERE watchlist_id = ?
  AND user_id = ?"
 );

 $stmt->bind_param("ii", $watchlistId, $userId);
 $stmt->execute();

 return [
  "success" => $stmt->affected_rows > 0
 ];
}

function addWatchlistItem($userId, $watchlistId, $code)
{
 global $conn;

 $code = strtoupper(trim($code));

 $stmt = $conn->prepare(
  "INSERT IGNORE INTO watchlist_items (watchlist_id, currency_id)
  SELECT w.watchlist_id, c.currency_id
  FROM watchlists w, currencies c
  WHERE w.watchlist_id = ?
  AND w.user_id = ?
  AND c.currency_code = ?"
 );

 $stmt->bind_param("iis", $watchlistId, $userId, $code);
 $stmt->execute();

 return ["success" => true];
}

function removeWatchlistItem($userId, $watchlistId, $code)
{
 global $conn;

 $code = strtoupper(trim($code));

 $stmt = $conn->prepare(
  "DELETE i FROM watchlist_items i
  JOIN watchlists w ON w.watchlist_id = i.watchlist_id
  JOIN currencies c ON c.currency_id = i.currency_id
  WHERE w.watchlist_id = ?
  AND w.user_id = ?
  AND c.currency_code = ?"
 );

 $stmt->bind_param("iis", $watchlistId, $userId, $code);
 $stmt->execute();

 return ["success" => true];
}
