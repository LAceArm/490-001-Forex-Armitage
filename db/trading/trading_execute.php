<?php
function executeTrade($userId, $fromCode, $toCode, $fromAmount)
{
    global $conn;

    $fromCode   = strtoupper(trim($fromCode));
    $toCode     = strtoupper(trim($toCode));
    $fromAmount = (float)$fromAmount;

    //makes sure number isn't negative or zero
    if ($fromAmount <= 0)
    return ["success" => false, "error" => "Invalid amount"];

    //makes sure it doesn't make s useless trade like trading USD to USD
    if ($fromCode == $toCode)
    return ["success" => false, "error" => "Choose different currencies"];

    $fromCurrency = getCurrencyInfo($fromCode);
    $toCurrency   = getCurrencyInfo($toCode);

    // stops the trade if currency doesn't exist or has no rate
    if (!$fromCurrency || !$toCurrency)
    return ["success" => false, "error" => "Currency not found"];

    $toAmount = $fromAmount / $fromCurrency["units_per_usd"] * $toCurrency["units_per_usd"];
    $tradeRate = $toAmount / $fromAmount;

    try {

        $conn->begin_transaction();

        if (!sellCurrency($userId, $fromCurrency["currency_id"], $fromAmount)) {
            $conn->rollback();
            return ["success" => false, "error" => "Insufficient balance"];
        }

        buyCurrency($userId, $toCurrency["currency_id"], $toAmount);

        saveTrade(
            $userId,
            $fromCurrency["currency_id"],
            $toCurrency["currency_id"],
            $fromAmount,
            $toAmount,
            $tradeRate
        );

        $conn->commit();

    } catch (Exception $e) {
        $conn->rollback();
        return ["success" => false, "error" => "Trade failed"];
    }

    return [
        "success"     => true,
        "from"        => $fromCode,
        "to"          => $toCode,
        "from_amount" => $fromAmount,
        "to_amount"   => $toAmount,
        "rate"        => $tradeRate
    ];
}
?>
