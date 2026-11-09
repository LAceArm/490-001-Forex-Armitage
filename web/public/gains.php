<?php
require_once __DIR__ . '/../broker/session_check.php'; // Checks if session key is valid
require_once __DIR__ . '/../helpers/format.php';

$key = $_SESSION['session_key'];

// Asks DB side for user's holdings
$portfolio = send_to_broker([
    'type' => 'get_portfolio',
    'session_key' => $key
]);

// Asks DB side for user's trade history
$tradesReply = send_to_broker([
    'type' => 'get_trades',
    'session_key' => $key
]);

// Per currency: total USD spent and total units bought (only trades paid for with USD)
$spent = [];
$bought = [];

foreach ($tradesReply['trades'] as $trade) {
    if ($trade['from_code'] == 'USD' && $trade['to_code'] != 'USD') {
        $currencyCode = $trade['to_code'];

        if (!isset($spent[$currencyCode])) {
            $spent[$currencyCode] = 0;
            $bought[$currencyCode] = 0;
        }

        $spent[$currencyCode] += $trade['from_amount'];
        $bought[$currencyCode] += $trade['to_amount'];
    }
}

$gainRows = []; // One row per currency owned
$currentValue = 0;
$message = '';

foreach ($portfolio['holdings'] as $holding) {
    // Sold-out currencies have nothing to measure
    if ($holding['amount'] == 0) {
        continue;
    }

    $currencyCode = $holding['currency_code'];
    $amount = $holding['amount'];
    $currentRate = $holding['usd_value'] / $amount; // USD value of 1 unit

    // Average USD paid per unit (USD itself is always 1)
    $purchaseRate = null;
    if ($currencyCode == 'USD') {
        $purchaseRate = 1;
    } elseif (isset($bought[$currencyCode])) {
        $purchaseRate = $spent[$currencyCode] / $bought[$currencyCode];
    }

    // Gain or loss = amount owned * (current rate - purchase rate)
    $gain = null;
    if ($purchaseRate !== null) {
        $gain = $amount * ($currentRate - $purchaseRate);
    } else {
        $message = 'Purchase rates are only available for currencies bought with USD.';
    }

    $gainRows[] = [
        'currency_code' => $currencyCode,
        'amount' => $amount,
        'purchase_rate' => $purchaseRate,
        'current_rate' => $currentRate,
        'gain' => $gain
    ];

    $currentValue += $holding['usd_value'];
}

if (empty($gainRows)) {
    $message = 'Portfolio performance information is not available yet.';
}

// Overall performance
$initialValue = $portfolio['starting_cash'];
$totalGain = $currentValue - $initialValue;
$performance = $totalGain / $initialValue * 100;

// Green text for a gain, red for a loss
function gainClass($value) {
    if ($value === null) {
        return '';
    }

    return $value >= 0 ? 'positive' : 'negative';
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
                <p><?= formatMoney($currentValue) ?></p>
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
                    <?= number_format($performance, 2) . '%' ?>
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
                    <tbody>
                        <?php foreach ($gainRows as $gainRow): ?>
                            <tr>
                                <td><?= $gainRow['currency_code'] ?></td>
                                <td><?= number_format($gainRow['amount'], 2) ?></td>
                                <td><?= formatRate($gainRow['purchase_rate']) ?></td>
                                <td><?= formatRate($gainRow['current_rate']) ?></td>
                                <td class="<?= gainClass($gainRow['gain']) ?>">
                                    <?= formatMoney($gainRow['gain']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

            <?php if ($message != ''): ?>
                <p class="gains-message">
                    <?= $message ?>
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
