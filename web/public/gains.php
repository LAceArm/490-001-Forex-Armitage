<?php
// ---------------------------------------------------------------------------
// gains.php: how the portfolio has performed since the account started.
//
// Cards: initial value (the starting cash) against current value, with the
// difference as the gain, so they match the Portfolio page's total.
// Table: for each holding, the average USD price paid vs. today's price.
// ---------------------------------------------------------------------------

// Only logged-in users get past this line. It checks the session key with the DB.
require_once __DIR__ . '/../broker/session_check.php';

$key = $_SESSION['session_key'];

// Small helpers: escape text for HTML, format money (with a minus sign
// in front of the $), format a rate, and pick the CSS class for a gain or loss.
$e       = fn($v) => htmlspecialchars((string)$v);
$money   = fn($v) => number_format((float)$v, 2);
$usd     = fn($v) => (round($v, 2) < 0 ? '-' : '') . '$' . number_format(abs($v), 2);
$fmtRate = fn($r) => number_format($r, $r < 0.01 ? 6 : 4);
$cls     = fn($v) => $v === null ? '' : (round($v, 2) >= 0 ? 'positive' : 'negative');

// Today's USD price of one unit of each currency. The DB stores rates as
// units per 1 USD, so the price is 1 / rate. USD itself is always 1.
$prices = ['USD' => 1.0];
$ratesReply = send_to_broker(['type' => 'get_rates']);
foreach ($ratesReply['rates'] ?? [] as $row) {
    if ($row['base_currency'] === 'USD' && (float)$row['rate'] > 0) {
        $prices[$row['quote_currency']] = 1 / (float)$row['rate'];
    }
}

// The user's positions and starting cash. $loaded is false if the request
// failed, so the page says "Not available" instead of showing zeros.
$portfolio = send_to_broker(['type' => 'get_portfolio', 'session_key' => $key]);
$loaded    = !empty($portfolio['success']);
$positions = $loaded ? $portfolio['positions'] : [];

// Average purchase price per currency, in USD per unit: total USD spent
// divided by total units bought. Only trades paid for in USD count, which is
// what the Marketplace page makes.
$spent = [];
$bought = [];
$tradesReply = send_to_broker(['type' => 'get_trades', 'session_key' => $key]);
foreach ($tradesReply['trades'] ?? [] as $t) {
    if ($t['from_code'] === 'USD' && $t['to_code'] !== 'USD') {
        $spent[$t['to_code']]  = ($spent[$t['to_code']]  ?? 0) + (float)$t['from_amount'];
        $bought[$t['to_code']] = ($bought[$t['to_code']] ?? 0) + (float)$t['to_amount'];
    }
}
$purchase = ['USD' => 1.0];
foreach ($bought as $code => $units) {
    if ($units > 0) {
        $purchase[$code] = $spent[$code] / $units;
    }
}

// One row per holding, plus the total value of everything held.
$holdings = [];
$total = 0.0;
foreach ($positions as $p) {
    $code    = $p['currency'];
    $amount  = (float)$p['amount'];
    $current = $prices[$code] ?? null;
    $paid    = $purchase[$code] ?? null;

    $holdings[] = [
        'code'          => $code,
        'amount'        => $amount,
        'purchase_rate' => $paid,
        'current_rate'  => $current,
        // Unrealized gain: units held times the change in price. Unknown if either price is.
        'gain'          => ($paid !== null && $current !== null) ? $amount * ($current - $paid) : null,
    ];
    $total += (float)($p['usd_value'] ?? 0);   // null means no rate, so it counts as 0
}

// Card figures. Everything starts from the starting cash, so the gain here
// also includes profit already locked in by selling.
$initialValue   = $loaded ? (float)$portfolio['starting_cash'] : null;
$currentValue   = $loaded ? $total : null;
$totalGain      = $loaded ? $total - $initialValue : null;
$gainPercentage = ($loaded && $initialValue > 0) ? $totalGain / $initialValue * 100 : null;

$cards = [
    'Initial Holdings Value' => [$initialValue === null ? 'Not available' : $usd($initialValue), ''],
    'Current Holdings Value' => [$currentValue === null ? 'Not available' : $usd($currentValue), ''],
    'Total Gain / Loss'      => [$totalGain === null ? 'Not available' : $usd($totalGain), $cls($totalGain)],
    'Performance'            => [$gainPercentage === null ? 'Not available'
                                    : (round($gainPercentage, 2) < 0 ? '-' : '') . number_format(abs($gainPercentage), 2) . '%',
                                 $cls($gainPercentage)],
];

// Message under the table, only if there is something to say.
$message = '';
if (!$loaded || !$holdings) {
    $message = 'Portfolio performance information is not available yet.';
} elseif (in_array(null, array_column($holdings, 'purchase_rate'), true)) {
    $message = 'Purchase rates are only available for currencies bought with USD.';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Portfolio Gains | Forex Armitage</title>

    <!-- Shared site styles, then styles for this page -->
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/gains.css">
</head>

<body class="home-page">

    <!-- Navigation bar (same links as the other pages) -->
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

        <!-- Four summary cards; gain and performance turn green or red -->
        <div class="gains-summary">
            <?php foreach ($cards as $label => [$text, $class]): ?>
                <div class="gains-card">
                    <h2><?= $label ?></h2>
                    <p class="<?= $class ?>"><?= $text ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- One row per currency held -->
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
                        <?php foreach ($holdings as $h): ?>
                            <tr>
                                <td><?= $e($h['code']) ?></td>
                                <td><?= $money($h['amount']) ?></td>
                                <td><?= $h['purchase_rate'] !== null ? $fmtRate($h['purchase_rate']) : 'Not available' ?></td>
                                <td><?= $h['current_rate'] !== null ? $fmtRate($h['current_rate']) : 'Not available' ?></td>
                                <td class="<?= $cls($h['gain']) ?>">
                                    <?= $h['gain'] !== null ? $usd($h['gain']) : 'Not available' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

            <?php if ($message !== ''): ?>
                <p class="gains-message"><?= $e($message) ?></p>
            <?php endif; ?>

        </section>

        <div class="gains-actions">
            <a href="portfolio.php" class="gains-button">View Portfolio</a>
            <a href="home.php" class="gains-back-button">Back to Home</a>
        </div>

    </main>

</body>
</html>