<?php
require_once __DIR__ . '/../broker/session_check.php';

$key = $_SESSION['session_key'];

$ratesReply = send_to_broker(['type' => 'get_rates']);

$prices = ['USD' => 1];

foreach ($ratesReply['rates'] ?? [] as $rate) {
    if ($rate['units_per_usd'] > 0) {
        $prices[$rate['currency_code']] = 1 / $rate['units_per_usd'];
    }
}
//get the users portfolio
$portfolio = send_to_broker([
    'type' => 'get_portfolio',
    'session_key' => $key
]);

$loaded = !empty($portfolio['success']);

$positions = [];

if ($loaded) {
    if (isset($portfolio['positions'])) {
        $positions = $portfolio['positions'];
    }
}
//get the users trade history
$tradesReply = send_to_broker([
    'type' => 'get_trades',
    'session_key' => $key
]);

$spent = [];
$bought = [];

foreach ($tradesReply['trades'] ?? [] as $trade) {
    if ($trade['from_code'] == 'USD' && $trade['to_code'] != 'USD') {
        $code = $trade['to_code'];

        if (!isset($spent[$code])) {
            $spent[$code] = 0;
            $bought[$code] = 0;
        }

        $spent[$code] += $trade['from_amount'];
        $bought[$code] += $trade['to_amount'];
    }
}
//calculate the average purchase price
//the calculation is purchase price = total usd spent / the total units baught
$purchase = ['USD' => 1];

foreach ($bought as $code => $amount) {
    if ($amount > 0) {
        $purchase[$code] = $spent[$code] / $amount;
    }
}
//calculate each currencys gain or loss
$holdings = [];
$currentValue = 0;

foreach ($positions as $position) {
    $code = $position['currency'];
    $amount = (float)$position['amount'];
//get the purchase and current rates
    $currentRate = $prices[$code] ?? null;
    $purchaseRate = $purchase[$code] ?? null;
//calculate the gain
    $gain = null;

    if ($currentRate !== null && $purchaseRate !== null) {
        $gain = $amount * ($currentRate - $purchaseRate);
    }
//store the output of the calculation
    $holdings[] = [
        'code' => $code,
        'amount' => $amount,
        'purchase_rate' => $purchaseRate,
        'current_rate' => $currentRate,
        'gain' => $gain
    ];

    $currentValue += (float)($position['usd_value'] ?? 0);
}
//calculates the portfolios overall performance
$initialValue = null;
$totalGain = null;
$performance = null;
//total gain =current value - initial value
//performance = total gain / initial value * 100
if ($loaded) {
    $initialValue = (float)$portfolio['starting_cash'];
    $totalGain = $currentValue - $initialValue;

    if ($initialValue > 0) {
        $performance = ($totalGain / $initialValue) * 100;
    }
}

$message = '';

if (!$loaded || empty($holdings)) {
    $message = 'Portfolio performance information is not available yet.';
} else {
    foreach ($holdings as $holding) {
        if ($holding['purchase_rate'] === null) {
            $message = 'Purchase rates are only available for currencies bought with USD.';
            break;
        }
    }
}
//this function displays the numbers as money
//example 1500 becomes $1,500.00
function formatMoney($value) {
    if ($value === null) {
        return 'Not available';
    }

    if ($value < 0) {
        return '-$' . number_format(abs($value), 2);
    }

    return '$' . number_format($value, 2);
}
//changes the color of the text depending if it gained or loss
function gainClass($value) {
    if ($value === null) {
        return '';
    }

    if (round($value, 2) >= 0) {
        return 'positive';
    }

    return 'negative';
}
//formats rate if it exists 
function formatRate($rate) {
    if ($rate === null) {
        return 'Not available';
    }

    return number_format($rate, 6);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Gains | Forex Armitage</title>

    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/gains.css">
</head>

<body class="home-page">

    <nav class="main-navbar">
        <div class="main-nav-container">
            <a href="home.php" class="main-logo">Forex Armitage</a>

            <div class="main-nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="notifications.php">Notifications</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <main class="gains-container">

        <div class="gains-header">
            <h1>Portfolio Gains</h1>
            <p>Track the performance of your virtual currency holdings.</p>
        </div>

        <div class="gains-summary">

            <div class="gains-card">
                <h2>Initial Holdings Value</h2>
                <p><?= formatMoney($initialValue) ?></p>
            </div>

            <div class="gains-card">
                <h2>Current Holdings Value</h2>
                <p><?= $loaded ? formatMoney($currentValue) : 'Not available' ?></p>
            </div>

            <div class="gains-card">
                <h2>Total Gain / Loss</h2>
                <p class="<?= gainClass($totalGain) ?>">
                    <?= formatMoney($totalGain) ?>
                </p>
            </div>

            <div class="gains-card">
                <h2>Performance</h2>
                <p class="<?= gainClass($performance) ?>">
                    <?= $performance !== null ? number_format($performance, 2) . '%' : 'Not available' ?>
                </p>
            </div>

        </div>

        <section class="gains-holdings">

            <h2>Currency Performance</h2>

            <div class="gains-table-wrapper">
                <table class="gains-table">

                    <thead>
                        <tr>
                            <th>Currency</th>
                            <th>Amount Owned</th>
                            <th>Purchase Rate (USD)</th>
                            <th>Current Rate (USD)</th>
                            <th>Gain / Loss</th>
                        </tr>
                    </thead>
<!--displays each currency
the foreach loop creates one row for every currency owned-->
                    <tbody>
                        <?php foreach ($holdings as $holding): ?>
                            <tr>
                                <td><?= htmlspecialchars($holding['code']) ?></td>

                                <td>
                                    <?= number_format($holding['amount'], 2) ?>
                                </td>

                                <td>
                                    <?= formatRate($holding['purchase_rate']) ?>
                                </td>

                                <td>
                                    <?= formatRate($holding['current_rate']) ?>
                                </td>

                                <td class="<?= gainClass($holding['gain']) ?>">
                                    <?= formatMoney($holding['gain']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

            <?php if ($message != ''): ?>
                <p class="gains-message">
                    <?= htmlspecialchars($message) ?>
                </p>
            <?php endif; ?>

        </section>

        <div class="gains-actions">
            <a href="portfolio.php" class="gains-button">View Portfolio</a>
            <a href="home.php" class="gains-back-button">Back to Home</a>
        </div>

    </main>

</body>
</html>
