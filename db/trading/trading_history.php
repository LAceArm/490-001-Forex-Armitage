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
    $stmt->bind_param("iiiddd", $userId, $fromCurrencyId, $toCurrencyId, $fromAmount, $toAmount, $tradeRate);
    $stmt->execute();

    return true;
}

    // A user's trades, newest first, with currency codes instead of ids
function getTrades($userId)
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT t.trade_id, 
                fc.currency_code AS from_code, 
                tc.currency_code AS to_code,
                t.rate AS trade_rate,
                t.from_amount, t.to_amount, t.created_at
        FROM trades t
        JOIN currencies fc ON fc.currency_id = t.from_currency_id
        JOIN currencies tc ON tc.currency_id = t.to_currency_id
        WHERE t.user_id = ?
        ORDER BY t.trade_id DESC
        LIMIT 1000"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $trades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    return ["success" => true, "trades" => $trades];
}
?>
