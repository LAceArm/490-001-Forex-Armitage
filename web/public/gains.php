<?php
require_once __DIR__ . '/../broker/session_check.php'; // Checks if session key is valid
require_once __DIR__ . '/../helpers/format.php';

// Asks DB side for user's holdings
$portfolioReply = send_to_broker([
    'type' => 'get_portfolio',
    'session_key' => $_SESSION['session_key']
]);

// Asks DB side for user's trade history
$tradesReply = send_to_broker([
    'type' => 'get_trades',
    'session_key' => $_SESSION['session_key']
]);

// Per currency: total USD spent and total units bought (only trades paid for with USD)
$usdSpent = [];
$unitsBought = [];

foreach ($tradesReply['trades'] as $trade) {
    if ($trade['from_code'] == 'USD') {
        $toCode = $trade['to_code'];
        $usdSpent[$toCode] = ($usdSpent[$toCode] ?? 0) + $trade['from_amount'];
        $unitsBought[$toCode] = ($unitsBought[$toCode] ?? 0) + $trade['to_amount'];
    }
}

$gainRows = []; // One row per currency owned
$portfolioValue = 0;

foreach ($portfolioReply['holdings'] as $holding) {
    $portfolioValue += $holding['usd_value'];

    // Sold-out currencies have nothing to measure
    if ($holding['amount'] == 0) {
        continue;
    }

    $toCode = $holding['currency_code'];
    $usdPerUnit = $holding['usd_value'] / $holding['amount']; // USD value of 1 unit

    // Average USD paid per unit (USD itself is always 1)
    $purchaseUsdPerUnit = null;
    if ($toCode == 'USD') {
        $purchaseUsdPerUnit = 1;
    } elseif (isset($unitsBought[$toCode])) {
        $purchaseUsdPerUnit = $usdSpent[$toCode] / $unitsBought[$toCode];
    }

    // Gain or loss = amount owned * (current rate - purchase rate)
    $gain = null;
    if ($purchaseUsdPerUnit !== null) {
        $gain = $holding['amount'] * ($usdPerUnit = $purchaseUsdPerUnit);
    }

    $gainRows[] = [
        'currency_code' => $toCode,
        'amount' => $holding['amount'],
        'purchase_rate' => $purchaseUsdPerUnit,
        'current_rate' => $usdPerUnit,
        'gain' => $gain
    ];
}

// Overall performance
$startingCash = $portfolioReply['starting_cash'];
$totalGain = $portfolioValue - $startingCash;
$performancePercent = $totalGain / $startingCash * 100;
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
                <h2>Initial Portfolio Value</h2>
                <p><?= formatMoney($startingCash) ?></p>
            </div>

            <div class="gains-card">
                <h2>Current Portfolio Value</h2>
                <p><?= formatMoney($portfolioValue) ?></p>
            </div>

            <div class="gains-card">
                <h2>Total Gain / Loss</h2>
                <p class="<?= gainClass($totalGain) ?>">
                    <?= formatMoney($totalGain) ?>
                </p>
            </div>

            <div class="gains-card">
                <h2>Performance</h2>
                <p class="<?= gainClass($performancePercent) ?>">
                    <?= number_format($performancePercent, 2) . '%' ?>
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
                                <td><?= formatRate($gainRow['purchase_usd_per_unit']) ?></td>
                                <td><?= formatRate($gainRow['usd_per_unit']) ?></td>
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
