<?php

// Saves one finished trade
function saveTrade($userId, $fromCurrencyId, $toCurrencyId, $fromAmount, $toAmount, $tradeRate) 
{
    global $conn;

    $stmt = $conn->prepare(
        "INSERT INTO trades
        (user_id, from_currency_id, to_currency_id, from_amount, to_amount, rate)
        VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("iiiddd", $userId, $fromId, $toId, $amount, $toAmount, $rate);
    $stmt->execute();

    return true;
}

    // A user's trades, newest first, with currency codes instead of ids
function getTrades($userId)
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT t.trade_id, f.currency_code AS from_code, k.currency_code AS to_code,
        t.from_amount, t.to_amount, t.rate, t.created_at
        FROM trades t
        JOIN currencies f ON f.currency_id = t.from_currency_id
        JOIN currencies k ON k.currency_id = t.to_currency_id
        WHERE t.user_id=?
        ORDER BY t.trade_id DESC
        LIMIT 1000"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $trades = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $trades[] = $row;
    }

    return ["success" => true, "trades" => $trades];
}
?>
