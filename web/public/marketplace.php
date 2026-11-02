
<?php
require_once __DIR__ . '/../broker/session_check.php';

$key = $_SESSION['session_key'];

function e($value) {
    return htmlspecialchars((string)$value);
}

$currencies = [];
$rates = send_to_broker(['type' => 'get_rates']);

foreach ($rates['rates'] ?? [] as $row) {
    if ($row['base_currency'] == 'USD' && $row['rate'] > 0) {
        $currencies[$row['quote_currency']] = 1 / $row['rate'];
    }
}

ksort($currencies);

$type = $_POST['trade_type'] ?? 'buy';
$currency = $_POST['currency'] ?? '';
$amount = $_POST['amount'] ?? '';
$rate = $currencies[$currency] ?? null;
$total = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($type != 'buy' && $type != 'sell') {
        $error = 'Invalid trade type.';
    } elseif ($rate === null) {
        $error = 'Please select a currency.';
    } elseif (!is_numeric($amount) || (float)$amount <= 0) {
        $error = 'Please enter a valid amount.';
    } else {
        $total = (float)$amount * $rate;

        if (($_POST['action'] ?? '') == 'confirm') {
            if ($type == 'buy') {
                $from = 'USD';
                $to = $currency;
                $tradeAmount = $total;
            } else {
                $from = $currency;
                $to = 'USD';
                $tradeAmount = (float)$amount;
            }

            $result = send_to_broker([
                'type' => 'trade',
                'session_key' => $key,
                'from' => $from,
                'to' => $to,
                'amount' => $tradeAmount
            ]);

            if (!empty($result['success'])) {
                $_SESSION['flash'] = 'Traded ' .
                    number_format($result['from_amount'], 2) . ' ' .
                    $result['from'] . ' for ' .
                    number_format($result['to_amount'], 2) . ' ' .
                    $result['to'];
            } else {
                $_SESSION['flash'] = $result['error'] ?? 'Trade failed.';
            }

            header('Location: marketplace.php');
            exit;
        }
    }
}

$message = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

if ($message == '') {
    if ($error != '') {
        $message = $error;
    } elseif ($total !== null) {
        $message = 'This is an estimated trade preview.';
    } else {
        $message = 'Select a currency and enter an amount to preview a trade.';
    }
}

$holdings = [];
$portfolio = send_to_broker([
    'type' => 'get_portfolio',
    'session_key' => $key
]);

foreach ($portfolio['positions'] ?? [] as $row) {
    $holdings[$row['currency']] = $row['amount'];
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

        <section class="marketplace-card">
            <h2>Trade Currency</h2>

            <form method="POST">
                <label>Trade Type</label>
                <select name="trade_type">
                    <option value="buy" <?= $type == 'buy' ? 'selected' : '' ?>>Buy</option>
                    <option value="sell" <?= $type == 'sell' ? 'selected' : '' ?>>Sell</option>
                </select>

                <label>Select Currency</label>
                <select name="currency" required>
                    <option value="">Select a currency</option>
                    <?php foreach ($currencies as $code => $price) { ?>
                        <option value="<?= e($code) ?>" <?= $currency == $code ? 'selected' : '' ?>>
                            <?= e($code) ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Amount</label>
                <input type="number" name="amount" min="0.01" step="any"
                       placeholder="Enter currency amount" value="<?= e($amount) ?>" required>

                <label>Exchange Rate (USD)</label>
                <input type="text" readonly value="<?php
                    if ($rate !== null) {
                        echo number_format($rate, $rate < 0.01 ? 6 : 4);
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
                    <strong><?= $total !== null ? e($currency) : 'Not selected' ?></strong>
                </div>
                <div class="summary-row">
                    <span>Amount</span>
                    <strong><?= $total !== null ? number_format((float)$amount, 2) : '—' ?></strong>
                </div>
                <div class="summary-row">
                    <span>Exchange Rate</span>
                    <strong><?= $total !== null ? number_format($rate, $rate < 0.01 ? 6 : 4) : '—' ?></strong>
                </div>
                <div class="summary-row total-row">
                    <span>Estimated Total (USD)</span>
                    <strong><?= $total !== null ? '$' . number_format($total, 2) : '—' ?></strong>
                </div>
            </div>

            <p class="trade-message" aria-live="polite"><?= e($message) ?></p>

            <form method="POST">
                <input type="hidden" name="trade_type" value="<?= e($type) ?>">
                <input type="hidden" name="currency" value="<?= e($currency) ?>">
                <input type="hidden" name="amount" value="<?= e($amount) ?>">

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
                    <span><?= e($code) ?></span>
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
