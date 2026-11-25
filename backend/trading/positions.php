<?php
// Adds an amount of a currency to a user's holdings
function buyCurrency($userId, $currencyId, $amount)
{
    global $conn;

    $stmt = $conn->prepare(
        "INSERT INTO positions (user_id, currency_id, amount)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE amount = amount + VALUES(amount)"
    );
    $stmt->bind_param("iid", $userId, $currencyId, $amount);
    $stmt->execute();

    return true;
}

// Removes an amount of a currency from a user's holdings
// Returns false if they don't have enough
function sellCurrency($userId, $currencyId, $amount)
{
    global $conn;

    // Lock the row so two trades can't spend the same money
    $stmt = $conn->prepare(
        "SELECT amount FROM positions
        WHERE user_id = ? AND currency_id = ? FOR UPDATE"
    );
    $stmt->bind_param("ii", $userId, $currencyId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc;

    if (!$row || $row["amount"] < $amount) {
        return false;
    }

    $stmt = $conn->prepare(
        "UPDATE positions
        SET amount = amount - ?
        WHERE user_id = ? AND currency_id = ?"
    );
    $stmt->bind_param("ii", $userId, $currencyId);
    $stmt->execute();

    return true;
}