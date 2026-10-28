
<?php
// ---------------------------------------------------------------------------
// marketplace.php: preview and confirm currency trades.
// Every trade is against USD: "Buy" pays USD for the chosen currency, and
// "Sell" turns the chosen currency back into USD.
// ---------------------------------------------------------------------------

// Only logged-in users get past this line. It checks the session key with the DB.
require_once __DIR__ . '/../broker/session_check.php';

$key = $_SESSION['session_key'];

// Small helpers: escape text for HTML, and format a rate (more decimals for
// tiny rates like JPY, so they don't show as 0.0063).
$e       = fn($v) => htmlspecialchars((string)$v);
$fmtRate = fn($r) => number_format($r, $r < 0.01 ? 6 : 4);

// Currency code => price of one unit in USD. The DB stores rates as units per
// 1 USD, so the price is 1 / rate.
$currencies = [];
$ratesReply = send_to_broker(['type' => 'get_rates']);
foreach ($ratesReply['rates'] ?? [] as $row) {
    if ($row['base_currency'] === 'USD' && (float)$row['rate'] > 0) {
        $currencies[$row['quote_currency']] = 1 / (float)$row['rate'];
    }
}
ksort($currencies);   // alphabetical dropdown

// Form values, with defaults for the first visit.
$tradeType      = $_POST['trade_type'] ?? 'buy';
$selectedCode   = $_POST['currency'] ?? '';
$amount         = $_POST['amount'] ?? '';
$rate           = $currencies[$selectedCode] ?? null;   // null if nothing valid is selected
$estimatedTotal = null;
$error          = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate the form, then work out the estimated USD total.
    if (!in_array($tradeType, ['buy', 'sell'], true)) {
        $error = 'Please select a valid trade type.';
    } elseif ($rate === null) {
        $error = 'Please select a valid currency.';
    } elseif (!is_numeric($amount) || (float)$amount <= 0) {
        $error = 'Please enter a valid amount.';
    } else {
        $estimatedTotal = (float)$amount * $rate;

        // "Confirm Trade" sends action=confirm; "Preview Trade" sends action=preview.
        if (($_POST['action'] ?? '') === 'confirm') {
            $buying = $tradeType === 'buy';

            // The listener finds the user from the session key and checks the balance.
            $r = send_to_broker([
                'type'        => 'trade',
                'session_key' => $key,
                'from'        => $buying ? 'USD' : $selectedCode,
                'to'          => $buying ? $selectedCode : 'USD',
                'amount'      => $buying ? $estimatedTotal : (float)$amount,
            ]);

            // One-time message for the next page load: the result or the error.
            $_SESSION['flash'] = !empty($r['success'])
                ? sprintf('Traded %s %s for %s %s',
                    number_format($r['from_amount'], 2), $r['from'],
                    number_format($r['to_amount'], 2), $r['to'])
                : ($r['error'] ?? 'Trade failed');

            // Redirect so refreshing the page can't repeat the trade.
            header('Location: marketplace.php');
            exit;
        }
    }
}

// True once a valid trade has been previewed (the summary and Confirm use this).
$previewing = $estimatedTotal !== null;

// Message under the summary: last trade's result, else an error, else a hint.
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);   // show it only once

if ($flash !== '')       { $message = $flash; }
elseif ($error !== '')   { $message = $error; }
elseif ($previewing)     { $message = 'This is an estimated trade preview.'; }
else                     { $message = 'Select a currency and enter an amount to preview a trade.'; }

// Rows for the summary card (the total row is added separately below).
$summary = [
    'Trade Type'    => $previewing ? ucfirst($tradeType) : 'Not selected',
    'Currency'      => $previewing ? $selectedCode : 'Not selected',
    'Amount'        => $previewing ? number_format((float)$amount, 2) : '—',
    'Exchange Rate' => $previewing ? $fmtRate($rate) : '—',
];

// What the user holds, as currency code => amount.
$holdings = [];
$portfolio = send_to_broker(['type' => 'get_portfolio', 'session_key' => $key]);
foreach ($portfolio['positions'] ?? [] as $p) {
    $holdings[$p['currency']] = (float)$p['amount'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Marketplace | Forex Armitage</title>

    <!-- Shared site styles, then styles for this page -->
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/marketplace.css">
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

    <main class="marketplace-container">

        <div class="marketplace-header">
            <h1>Marketplace</h1>
            <p>Buy and sell virtual currencies using exchange rates.</p>
        </div>

        <div class="marketplace-grid">

            <!-- Left card: the trade form. Its one button is Preview Trade. -->
            <section class="marketplace-card">
                <h2>Trade Currency</h2>

                <form method="POST" action="marketplace.php">

                    <label for="trade-type">Trade Type</label>
                    <select id="trade-type" name="trade_type" required>
                        <option value="buy"  <?= $tradeType === 'buy'  ? 'selected' : '' ?>>Buy</option>
                        <option value="sell" <?= $tradeType === 'sell' ? 'selected' : '' ?>>Sell</option>
                    </select>

                    <label for="currency">Select Currency</label>
                    <select id="currency" name="currency" required>
                        <option value="">Select a currency</option>
                        <?php foreach ($currencies as $code => $price): ?>
                            <option value="<?= $e($code) ?>" <?= $selectedCode === $code ? 'selected' : '' ?>>
                                <?= $e($code) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="amount">Amount</label>
                    <input type="number" id="amount" name="amount"
                           min="0.01" step="any" placeholder="Enter currency amount"
                           value="<?= $e($amount) ?>" required>

                    <!-- USD price of one unit of the chosen currency (filled in by Preview) -->
                    <label for="exchange-rate">Exchange Rate (USD)</label>
                    <input type="text" id="exchange-rate" readonly
                           value="<?= $rate !== null ? $fmtRate($rate) : '—' ?>">

                    <button type="submit" name="action" value="preview" class="marketplace-button">
                        Preview Trade
                    </button>

                </form>
            </section>

            <!-- Right card: summary of the previewed trade, with its own Confirm form -->
            <section class="marketplace-card">
                <h2>Trade Summary</h2>

                <div class="trade-summary">
                    <?php foreach ($summary as $label => $value): ?>
                        <div class="summary-row">
                            <span><?= $label ?></span>
                            <strong><?= $e($value) ?></strong>
                        </div>
                    <?php endforeach; ?>

                    <div class="summary-row total-row">
                        <span>Estimated Total (USD)</span>
                        <strong><?= $previewing ? '$' . number_format($estimatedTotal, 2) : '—' ?></strong>
                    </div>
                </div>

                <!-- Last trade result, a form error, or a hint -->
                <p class="trade-message" aria-live="polite"><?= $e($message) ?></p>

                <!-- Confirm has its own form that carries the previewed values, so it
                     trades exactly what the summary shows, even if the fields on the
                     left were edited after previewing. Disabled until a valid preview. -->
                <form method="POST" action="marketplace.php">
                    <input type="hidden" name="trade_type" value="<?= $e($tradeType) ?>">
                    <input type="hidden" name="currency"   value="<?= $e($selectedCode) ?>">
                    <input type="hidden" name="amount"     value="<?= $e($amount) ?>">
                    <button type="submit" name="action" value="confirm"
                            class="confirm-button" <?= $previewing ? '' : 'disabled' ?>>
                        Confirm Trade
                    </button>
                </form>
            </section>

        </div>

        <!-- Current balances, so you can see a trade take effect -->
        <section class="marketplace-card">
            <h2>Your Holdings</h2>
            <div class="trade-summary">
                <?php foreach ($holdings as $code => $amt): ?>
                    <div class="summary-row">
                        <span><?= $e($code) ?></span>
                        <strong><?= number_format($amt, 2) ?></strong>
                    </div>
                <?php endforeach; ?>
                <?php if (!$holdings): ?>
                    <p>No holdings available.</p>
                <?php endif; ?>
            </div>
        </section>

        <div class="marketplace-footer">
            <a href="portfolio.php" class="back-portfolio-button">Back to Portfolio</a>
            <a href="home.php" class="back-portfolio-button">Back to Home</a>
        </div>

    </main>

</body>
</html>
