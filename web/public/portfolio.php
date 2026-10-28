<?php
// ---------------------------------------------------------------------------
// portfolio.php: what the user holds, what it's worth in USD, and the total.
// ---------------------------------------------------------------------------

// Only logged-in users get past this line. It checks the session key with the DB.
require_once __DIR__ . '/../broker/session_check.php';

// Readable names. The DB currently stores each code as its own name, so this
// map supplies them; anything not listed falls back to showing its code.
$names = [
    'AED' => 'United Arab Emirates Dirham',
    'CAD' => 'Canadian Dollar',
    'CHF' => 'Swiss Franc',
    'CNY' => 'Chinese Yuan',
    'EUR' => 'Euro',
    'GBP' => 'British Pound',
    'JPY' => 'Japanese Yen',
    'USD' => 'US Dollar',
];

// Small helpers: escape text for HTML, format money, and format a rate
// (more decimals for tiny rates like JPY so they don't show as 0.0063).
$e       = fn($v) => htmlspecialchars((string)$v);
$money   = fn($v) => number_format((float)$v, 2);
$fmtRate = fn($r) => number_format($r, $r < 0.01 ? 6 : 4);

// USD price of one unit of each currency. The DB stores rates as units per
// 1 USD, so the price is 1 / rate. USD itself is always 1.
$prices = ['USD' => 1.0];
$ratesReply = send_to_broker(['type' => 'get_rates']);
foreach ($ratesReply['rates'] ?? [] as $row) {
    if ($row['base_currency'] === 'USD' && (float)$row['rate'] > 0) {
        $prices[$row['quote_currency']] = 1 / (float)$row['rate'];
    }
}

// The user's positions, looked up by the listener from the session key.
// $loaded is false if the request failed, so the page says "Not available"
// instead of showing zeros.
$portfolio = send_to_broker(['type' => 'get_portfolio', 'session_key' => $_SESSION['session_key']]);
$loaded    = !empty($portfolio['success']);
$positions = $loaded ? $portfolio['positions'] : [];

// Cash is the USD balance; holdings value is every other position at current
// rates. The total adds the two, so the cards always agree with the table.
$cash = 0.0;
$holdingsValue = 0.0;
foreach ($positions as $p) {
    if ($p['currency'] === 'USD') {
        $cash += (float)$p['amount'];
    } else {
        $holdingsValue += (float)($p['usd_value'] ?? 0);   // null means no rate, so it counts as 0
    }
}

$cards = [
    'Available Cash'        => $cash,
    'Holdings Value'        => $holdingsValue,
    'Total Portfolio Value' => $cash + $holdingsValue,
];

// Message under the table: nothing loaded, a missing rate, or hidden.
if (!$loaded || !$positions) {
    $message = 'Portfolio information is not available yet.';
} elseif (!empty($portfolio['incomplete'])) {
    $message = 'Some values are unavailable because an exchange rate is missing.';
} else {
    $message = '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Portfolio | Forex Armitage</title>

    <!-- Shared site styles, then styles for this page -->
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/portfolio.css">
</head>

<body class="home-page">

    <!-- Navigation bar (same links as the other pages) -->
    <nav class="main-navbar">
        <div class="main-nav-container">
<<<<<<< HEAD
            <a href="home.php" class="main-logo">Currency Trading</a>
=======

            <a href="home.php" class="main-logo">
                Forex Armitage
            </a>
>>>>>>> 582041c (Fixing Header Name)

            <div class="main-nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="notifications.php">Notifications</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <main class="portfolio-container">

        <div class="portfolio-header">
            <h1>My Portfolio</h1>
            <p>View your virtual cash balance, currency holdings, and total portfolio value.</p>
        </div>

        <!-- Three summary cards, all in USD -->
        <div class="portfolio-summary">
            <?php foreach ($cards as $label => $value): ?>
                <div class="portfolio-card">
                    <h2><?= $label ?></h2>
                    <p><?= $loaded ? '$' . $money($value) : 'Not available' ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- One row per currency the user holds -->
        <section class="portfolio-holdings">

            <h2>My Currency Holdings</h2>

            <div class="portfolio-table-wrapper">
                <table class="portfolio-table">

                    <thead>
                        <tr>
                            <th>Currency Code</th>
                            <th>Currency Name</th>
                            <th>Amount Owned</th>
                            <th>Exchange Rate (USD)</th>
                            <th>Current Value (USD)</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($positions as $p): $code = $p['currency']; ?>
                            <tr>
                                <td><?= $e($code) ?></td>
                                <td><?= $e($names[$code] ?? $code) ?></td>
                                <td><?= $money($p['amount']) ?></td>
                                <td><?= isset($prices[$code]) ? $fmtRate($prices[$code]) : 'Not available' ?></td>
                                <td><?= $p['usd_value'] !== null ? '$' . $money($p['usd_value']) : 'Not available' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

            <!-- Only shown when there is something to explain -->
            <p class="portfolio-message" <?= $message === '' ? 'hidden' : '' ?>>
                <?= $e($message) ?>
            </p>

        </section>

        <div class="portfolio-actions">
            <a href="marketplace.php" class="portfolio-button">Go to Marketplace</a>
            <a href="home.php" class="back-home-button">Back to Home</a>
        </div>

    </main>

</body>
</html>