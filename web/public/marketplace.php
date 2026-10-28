
<?php
session_start();

//if (empty($_SESSION['user_id'])) {
  //  header('Location: login.php');
    //exit;
//}

$currencies = [];

$tradeType = $_POST['trade_type'] ?? 'buy';
$selectedCode = $_POST['currency'] ?? '';
$amount = $_POST['amount'] ?? '';
$selectedCurrency = null;
$estimatedTotal = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach ($currencies as $currency) {
        if ($currency['code'] === $selectedCode) {
            $selectedCurrency = $currency;
            break;
        }
    }

    if ($tradeType !== 'buy' && $tradeType !== 'sell') {
        $error = 'Please select a valid trade type.';
    } elseif ($selectedCurrency === null) {
        $error = 'Please select a valid currency.';
    } elseif (!is_numeric($amount) || (float)$amount <= 0) {
        $error = 'Please enter a valid amount.';
    } elseif (
        !isset($selectedCurrency['rate']) ||
        !is_numeric($selectedCurrency['rate']) ||
        (float)$selectedCurrency['rate'] <= 0
    ) {
        $error = 'Exchange rate is not available.';
    } else {
        $estimatedTotal =
            (float)$amount * (float)$selectedCurrency['rate'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Marketplace | Currency Trading</title>

    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/marketplace.css">
</head>

<body class="home-page">

    <nav class="main-navbar">
        <div class="main-nav-container">

            <a href="home.php" class="main-logo">
                Currency Trading
            </a>

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

                <form method="POST" action="marketplace.php">

                    <label for="trade-type">Trade Type</label>

                    <select id="trade-type" name="trade_type" required>

                        <option value="buy"
                            <?php echo $tradeType === 'buy' ? 'selected' : ''; ?>>
                            Buy
                        </option>

                        <option value="sell"
                            <?php echo $tradeType === 'sell' ? 'selected' : ''; ?>>
                            Sell
                        </option>

                    </select>

                    <label for="currency">Select Currency</label>

                    <select id="currency" name="currency" required>

                        <option value="">Select a currency</option>

                        <?php foreach ($currencies as $currency): ?>

                            <option
                                value="<?php echo htmlspecialchars($currency['code']); ?>"
                                <?php echo $selectedCode === $currency['code'] ? 'selected' : ''; ?>
                            >
                                <?php
                                echo htmlspecialchars(
                                    $currency['code'] . ' - ' . $currency['name']
                                );
                                ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <label for="amount">Amount</label>

                    <input
                        type="number"
                        id="amount"
                        name="amount"
                        min="0.01"
                        step="any"
                        placeholder="Enter currency amount"
                        value="<?php echo htmlspecialchars((string)$amount); ?>"
                        required
                    >

                    <label for="exchange-rate">Exchange Rate (USD)</label>

                    <input
                        type="text"
                        id="exchange-rate"
                        value="<?php
                            echo $selectedCurrency !== null
                                ? htmlspecialchars((string)($selectedCurrency['rate'] ?? 'Not available'))
                                : 'Not available';
                        ?>"
                        readonly
                    >

                    <button type="submit" class="marketplace-button">
                        Preview Trade
                    </button>

                </form>

            </section>

            <section class="marketplace-card">

                <h2>Trade Summary</h2>

                <div class="trade-summary">

                    <div class="summary-row">
                        <span>Trade Type</span>

                        <strong>
                            <?php
                            echo $estimatedTotal !== null
                                ? ucfirst($tradeType)
                                : 'Not selected';
                            ?>
                        </strong>
                    </div>

                    <div class="summary-row">
                        <span>Currency</span>

                        <strong>
                            <?php
                            echo $estimatedTotal !== null
                                ? htmlspecialchars($selectedCode)
                                : 'Not selected';
                            ?>
                        </strong>
                    </div>

                    <div class="summary-row">
                        <span>Amount</span>

                        <strong>
                            <?php
                            echo $estimatedTotal !== null
                                ? number_format((float)$amount, 2)
                                : '—';
                            ?>
                        </strong>
                    </div>

                    <div class="summary-row">
                        <span>Exchange Rate</span>

                        <strong>
                            <?php
                            echo $estimatedTotal !== null
                                ? number_format((float)$selectedCurrency['rate'], 4)
                                : '—';
                            ?>
                        </strong>
                    </div>

                    <div class="summary-row total-row">
                        <span>Estimated Total (USD)</span>

                        <strong>
                            <?php
                            echo $estimatedTotal !== null
                                ? '$' . number_format($estimatedTotal, 2)
                                : '—';
                            ?>
                        </strong>
                    </div>

                </div>

                <p class="trade-message">

                    <?php if ($error !== ''): ?>

                        <?php echo htmlspecialchars($error); ?>

                    <?php elseif ($estimatedTotal !== null): ?>

                        This is an estimated trade preview.

                    <?php else: ?>

                        Select a currency and enter an amount to preview a trade.

                    <?php endif; ?>

                </p>

                <button
                    type="button"
                    class="confirm-button"
                    disabled
                >
                    Confirm Trade
                </button>

            </section>

        </div>

        <div class="marketplace-footer">

            <a href="portfolio.php" class="back-portfolio-button">
                Back to Portfolio
            </a>

        </div>

    </main>

</body>
</html>
