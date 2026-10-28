<?php

require_once "database.php";

function tradeError($message)
{
        return ["success" => false, "error" => $message];
}

function currencyId($code)
{
        global $conn;

        $stmt = $conn->prepare("SELECT currency_id FROM currencies WHERE currency_code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? (int)$row["currency_id"] : null;
}

// Units of $code per 1 USD from the ratrse table. USD itself is 1
function usdRate($code)
{
        global $conn;

        if ($code === "USD") {
                return 1.0;
        }

        $stmt = $conn->prepare(
                "SELECT r.rate
                FROM rates r
                JOIN currencies b on b.currency_id = r.base_currency_id
                JOIN currencies q ON q.currency_id = r.quote_currency_id
                WHERE b.currency_code = 'USD' AND q.currency_code = ?"
        );
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? (float)$row["rate"] : null;
}

function executeTrade($userId, $fromCode, $toCode, $amount)
{
        global $conn;

        $fromCode = strtoupper(trim($fromCode));
        $toCode   = strtoupper(trim($toCode));
        $amount   = (float)$amount;

        if ($amount <= 0) {
                return tradeError("Amount must be greater than zero");
        }

        if ($fromCode === $toCode) {
                return tradeError("Choose two different currencies");
        }

        $fromId = currencyId($fromCode);
        $toId   = currencyId($toCode);
        if (!$fromId || !$toId) {
                return tradeError("Unknown currency");
        }

        $fromRate = usdRate($fromCode);
        $toRate   = usdRate($toCode);
        if (!$fromRate || !$toRate) {
                return tradeError("No rate available for that pair");
        }

        // Convert through USD: from -> USD -> to
        $toAmount = round($amount / $fromRate * $toRate, 8);
        $rate     = $toAmount / $amount;

        try {
                $conn->begin_transaction();

                // Lock the row we're spending from, then check the balance
                $stmt = $conn->prepare(
                        "SELECT amount FROM positions
                        WHERE user_id = ? AND currency_id = ? FOR UPDATE"
                );

                $stmt->bind_param("ii", $userId, $fromId);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();

                if (!$row || (float)$row["amount"] < $amount) {
                        $conn->rollback();
                        return tradeError("Insufficient balance");
                }

                $stmt = $conn->prepare(
                        "UPDATE positions SET amount = amount - ?
                        WHERE user_id = ? AND currency_id = ?"
                );

                $stmt->bind_param("dii", $amount, $userId, $fromId);
                $stmt->execute();

                $stmt = $conn->prepare(
                        "INSERT into positions (user_id, currency_id, amount)
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE amount = amount + VALUES(amount)"
                );

                $stmt->bind_param("iid", $userId, $toId, $toAmount);
                $stmt->execute();

                $stmt = $conn->prepare(
                        "INSERT INTO trades
                        (user_id, from_currency_id, to_currency_id, from_amount, to_amount, rate)
                        VALUES (?, ?, ?, ?, ?, ?)"
                );

                $stmt->bind_param("iiiddd", $userId, $fromId, $toId, $amount, $toAmount, $rate);
                $stmt->execute();

                $conn->commit();
        } catch (mysqli_sql_exception $e) {
                $conn->rollback();
                return tradeError("Trade failed");
        }

        return [
                "success"     => true,
                "from"        => $fromCode,
                "to"          => $toCode,
                "from_amount" => $amount,
                "to_amount"   => $toAmount,
                "rate"        => $rate
        ];
}
?>
