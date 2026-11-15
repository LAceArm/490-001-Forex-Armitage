<?php
function getPortfolio($userId)
{
    global $conn;

    $stmt = $conn->prepare("SELECT starting_cash FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $portfolio = $stmt->get_result()->fetch_assoc();

    $stmt = $conn->prepare(
        "SELECT c.currency_code,
            p.amount,
            IF(c.currency_code = 'USD', p.amount, p.amount / r.units_per_usd) AS usd_value
        FROM positions p
        JOIN currencies c ON c.currency_id = p.currency_id
        LEFT JOIN rates r ON r.currency_id = p.currency_id
        WHERE p.user_id = ?"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $holdings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    return [
        "success" => true,
        "starting_cash" => $portfolio["starting_cash"],
        "holdings" => $holdings
    ];
}
?>
