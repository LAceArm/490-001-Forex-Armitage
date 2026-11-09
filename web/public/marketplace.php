<?php
require_once __DIR__ . '/../broker/session_check.php'; // Loads session check
require_once __DIR__ . '/../helpers/format.php';

$key = $_SESSION['session_key'];

$currencyRows = [];

$rates = send_to_broker(['type' => 'get_rates']); // Bother DB for rates

foreach ($rates['rates'] as $row) {
    $currencyRows[$row['currency_code']] = 1 / $row['units_per_usd'];
}

ksort($currencyRows);

// Set variables
$type = ($_POST['trade_type'] ?? '') == 'sell' ? 'sell' : 'buy';
$currency = $_POST['currency'] ?? '';
$amount = $_POST['from_amount'] ?? '';
$rate = $currencyRows[$currency] ?? null;
$total = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $total = (float) $amount * $rate; // Calculates USD value of currency
    if ($_POST['action'] == 'confirm') {
        if ($type == 'buy') { // Buy: USD --> Other
            $from        = 'USD';
            $to          = $currency;
            $tradeAmount = $total;
        } else { // Sell: Other --> USD
            $from        = $currency;
            $to          = 'USD';
            $tradeAmount = (float) $amount;
        }

        $result = send_to_broker([
            'type'        => 'trade',
            'session_key' => $key,
            'from_code'   => $from,
            'to_code'     => $to,
            'from_amount' => $tradeAmount
        ]);

        // Displays confirmation or error message
        if (!empty($result['success'])) {
            // e.g., "Traded [amount][code] for [amount][code]"
            $_SESSION['flash'] = sprintf(
                'Traded %.2f %s for %.2f %s',
                $result['from_amount'], $result['from_code'],
                $result['to_amount'], $result['to_code']
            );
        } else {
            $_SESSION['flash'] = $result['error'];
        }

        header('Location: marketplace.php'); // Redirect to marketplace.php
        exit;
    }
}

if ($total === null) {
    $message = 'Select a currency and enter an amount to preview a trade.';
} else {
    $message = 'This is an estimated trade preview.';
}

$message = $_SESSION['flash'] ?? $message; // Load message to page
unset($_SESSION['flash']); 

// User's holdings
$holdings = [];
$portfolio = send_to_broker(['type' => 'get_portfolio', 'session_key' => $key]);
foreach ($portfolio['holdings'] as $row) {
    $holdings[$row['currency_code']] = $row['amount'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace | Forex Armitage</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/marketplace.css">
</head>

<body class="home-page">

<!-- Navigation Bar -->
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
<!--layout of  the page-->
<main class="marketplace-container">

    <div class="marketplace-header">
        <h1>Marketplace</h1>
        <p>Buy and sell virtual currencies using exchange rates.</p>
    </div>

    <div class="marketplace-grid">

        <section class="marketplace-card">
            <h2>Trade Currency</h2>
<!--make a form that sends info to the php side -->
            <form method="POST">
                <label>Trade Type</label>
                <select name="trade_type">
                    <option value="buy" <?= $type == 'buy' ? 'selected' : '' ?>>Buy</option>
                    <option value="sell" <?= $type == 'sell' ? 'selected' : '' ?>>Sell</option>
                </select>

                <label>Select Currency</label>
                <select name="currency" required>
                    <option value="">Select a currency</option>
                    <?php foreach ($currencyRows as $code => $price) { ?>
                        <option value="<?= $code ?>" <?= $currency == $code ? 'selected' : '' ?>>
                            <?= $code ?>
                        </option>
                    <?php } ?>
                </select>
<!--lets users decide how much they want to buy or sell-->
                <label>Amount</label>
                <input type="number" name="from_amount" min="0.01" step="any"
                       placeholder="Enter currency amount" value="<?= htmlspecialchars($amount) ?>" required>

                <label>Exchange Rate (USD)</label>
                <input type="text" readonly value="<?php
                    if ($rate !== null) {
                        echo formatRate($rate);
                    } else {
                        echo '—';
                    }
                ?>">

                <button type="submit" name="action" value="preview" class="marketplace-button">
                    Preview Trade
                </button>
            </form>
        </section>

        <section class="marketplace-card">
            <h2>Trade Summary</h2>

            <div class="trade-summary">
                <div class="summary-row">
                    <span>Trade Type</span>
                    <strong><?= $total !== null ? ucfirst($type) : 'Not selected' ?></strong>
                </div>
                <div class="summary-row">
                    <span>Currency</span>
                    <strong><?= $total !== null ? $currency : 'Not selected' ?></strong>
                </div>
                <div class="summary-row">
                    <span>Amount</span>
                    <strong><?= $total !== null ? number_format((float)$amount, 2) : '—' ?></strong>
                </div>
                <div class="summary-row">
                    <span>Exchange Rate</span>
                    <strong><?= $total !== null ? formatRate($rate) : '—' ?></strong>
                </div>
                <div class="summary-row total-row">
                    <span>Estimated Total (USD)</span>
                    <strong><?= $total !== null ? formatMoney($total) : '—' ?></strong>
                </div>
            </div>

            <p class="trade-message"><?= $message ?></p>
            <!-- this is a hidden input field -->
            <!-- it sends the same trade that the user made php side to
            confirm the trade when the button is clicked -->
            <form method="POST">
                <input type="hidden" name="trade_type" value="<?= $type ?>">
                <input type="hidden" name="currency" value="<?= $currency ?>">
                <input type="hidden" name="from_amount" value="<?= htmlspecialchars($amount) ?>">

                <button type="submit" name="action" value="confirm"
                        class="confirm-button" <?= $total === null ? 'disabled' : '' ?>>
                    Confirm Trade
                </button>
            </form>
        </section>

    </div>

    <section class="marketplace-card">
        <h2>Your Holdings</h2>
        <div class="trade-summary">
            <?php foreach ($holdings as $code => $value) { ?>
                <div class="summary-row">
                    <span><?= $code ?></span>
                    <strong><?= number_format((float)$value, 2) ?></strong>
                </div>
            <?php } ?>

            <?php if (empty($holdings)) { ?>
                <p>No holdings available.</p>
            <?php } ?>
        </div>
    </section>

    <div class="marketplace-footer">
        <a href="portfolio.php" class="back-portfolio-button">Back to Portfolio</a>
        <a href="home.php" class="back-portfolio-button">Back to Home</a>
    </div>

</main>
</body>
</html>
