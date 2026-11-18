
<?php
require_once __DIR__ . '/../broker/session_check.php'; // Loads session check
require_once __DIR__ . '/../helpers/format.php';

$key = $_SESSION['session_key'];
$currencyRows = [];

$rates = sendToBackend(['type' => 'get_rates']); // Bother DB for rates

foreach ($rates['rates'] as $row) {
    if ($row['units_per_usd'] > 0) {
        $currencyRows[$row['currency_code']] = 1 / $row['units_per_usd'];
    }
}

ksort($currencyRows);

// Set variables
$type = $_POST['trade_type'] ?? 'buy';
$currency = $_POST['currency'] ?? '';
$amount = $_POST['from_amount'] ?? '';
$rate = $currencyRows[$currency] ?? null;
$total = null;
$message = 'Select a currency and enter an amount to preview a trade.';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($rate == null || $currency == 'USD' || !is_numeric($amount) || $amount <= 0) {
        $message = 'Please enter a valid currency and amount.';
    } else {
        $total = $amount * $rate; // Calculates USD value of currency
        $message = 'This is an estimated trade preview.';

        if (($_POST['action'] ?? '') == 'confirm') {
            if ($type == 'buy') { // Buy: USD --> Other
                $from = 'USD';
                $to = $currency;
                $tradeAmount = $total;
            } else { // Sell: Other --> USD
                $from = $currency;
                $to = 'USD';
                $tradeAmount = $amount;
            }

            $result = sendToBackend([
                'type' => 'trade',
                'session_key' => $key,
                'from_code' => $from,
                'to_code' => $to,
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
                $_SESSION['flash'] = $result['error'] ?? 'Trade failed.';
            }

            header('Location: marketplace.php'); // Redirect to marketplace.php
            exit;
        }
    }
}

$message = $_SESSION['flash'] ?? $message; // Load message to page
unset($_SESSION['flash']);

// User's holdings
$holdings = [];
$portfolio = sendToBackend(['type' => 'get_portfolio', 'session_key' => $key]);

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<!-- Navigation Bar -->
<nav class="navbar bg-white shadow-sm py-3">
    <div class="container">
        <a href="home.php" class="navbar-brand fw-bold text-primary">Forex Armitage</a>
        <div class="d-flex gap-3 align-items-center">
            <a href="dashboard.php" class="text-dark text-decoration-none">Dashboard</a>
            <a href="notifications.php" class="text-dark text-decoration-none">Notifications</a>
            <a href="profile.php" class="text-dark text-decoration-none">Profile</a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<!--layout of  the page-->
<main class="container py-5">

    <div class="text-center mb-4">
        <h1>Marketplace</h1>
        <p class="text-secondary">Buy and sell virtual currencies using exchange rates.</p>
    </div>

    <div class="row g-4 mb-4">

        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <h2 class="fs-5 mb-4">Trade Currency</h2>

                    <!--make a form that sends info to the php side -->
                    <form method="POST">
                        <label class="form-label">Trade Type</label>
                        <select name="trade_type" class="form-select mb-3">
                            <option value="buy" <?= $type == 'buy' ? 'selected' : '' ?>>Buy</option>
                            <option value="sell" <?= $type == 'sell' ? 'selected' : '' ?>>Sell</option>
                        </select>

                        <label class="form-label">Select Currency</label>
                        <select name="currency" class="form-select mb-3" required>
                            <option value="">Select a currency</option>
                            <?php foreach ($currencyRows as $code => $price) { ?>
                                <?php if ($code != 'USD') { ?>
                                    <option value="<?= $code ?>" <?= $currency == $code ? 'selected' : '' ?>>
                                        <?= $code ?>
                                    </option>
                                <?php } ?>
                            <?php } ?>
                        </select>

                        <!--lets users decide how much they want to buy or sell-->
                        <label class="form-label">Amount</label>
                        <input type="number" name="from_amount" min="0.01" step="any"
                               class="form-control mb-3" placeholder="Enter currency amount"
                               value="<?= htmlspecialchars((string)$amount) ?>" required>

                        <label class="form-label">Exchange Rate (USD)</label>
                        <input type="text" class="form-control mb-4" readonly
                               value="<?= $rate !== null ? formatRate($rate) : '—' ?>">

                        <button type="submit" name="action" value="preview" class="btn btn-primary w-100">
                            Preview Trade
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <h2 class="fs-5 mb-4">Trade Summary</h2>

                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>Trade Type</span>
                        <strong><?= $total !== null ? ucfirst($type) : 'Not selected' ?></strong>
                    </div>

                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>Currency</span>
                        <strong><?= $total !== null ? htmlspecialchars($currency) : 'Not selected' ?></strong>
                    </div>

                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>Amount</span>
                        <strong><?= $total !== null ? number_format((float)$amount, 2) : '—' ?></strong>
                    </div>

                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>Exchange Rate</span>
                        <strong><?= $total !== null ? formatRate($rate) : '—' ?></strong>
                    </div>

                    <div class="d-flex justify-content-between py-3">
                        <strong>Estimated Total (USD)</strong>
                        <strong class="text-primary">
                            <?= $total !== null ? formatMoney($total) : '—' ?>
                        </strong>
                    </div>

                    <p class="alert alert-info mt-3"><?= htmlspecialchars($message) ?></p>

                    <!-- this is a hidden input field -->
                    <!-- it sends the same trade that the user made php side to
                    confirm the trade when the button is clicked -->
                    <form method="POST">
                        <input type="hidden" name="trade_type" value="<?= htmlspecialchars($type) ?>">
                        <input type="hidden" name="currency" value="<?= htmlspecialchars($currency) ?>">
                        <input type="hidden" name="from_amount" value="<?= htmlspecialchars((string)$amount) ?>">

                        <button type="submit" name="action" value="confirm"
                                class="btn btn-success w-100" <?= $total === null ? 'disabled' : '' ?>>
                            Confirm Trade
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="fs-5 mb-3">Your Holdings</h2>

            <?php if (empty($holdings)) { ?>
                <p>No holdings available.</p>
            <?php } else { ?>
                <table class="table table-bordered">
                    <tr><th>Currency</th><th>Amount Owned</th></tr>
                    <?php foreach ($holdings as $code => $value) { ?>
                        <tr>
                            <td><?= htmlspecialchars($code) ?></td>
                            <td><?= number_format((float)$value, 2) ?></td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } ?>
        </div>
    </div>

    <div class="text-center">
        <a href="portfolio.php" class="btn btn-primary me-2">Back to Portfolio</a>
        <a href="home.php" class="btn btn-outline-primary">Back to Home</a>
    </div>

</main>
</body>
</html>
