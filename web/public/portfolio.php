<?php
require_once __DIR__ . '/../broker/session_check.php'; // Checks if session key is valid
require_once __DIR__ . '/../helpers/format.php';

// Asks DB side for user's holdings
$portfolio = send_to_broker(array(
    'type' => 'get_portfolio',
    'session_key' => $_SESSION['session_key']
));

// 'Available Cash' and 'Holdings Value', respectively
$cash = 0;
$holdingsValue = 0;

// Go through each holding
foreach ($portfolio['holdings'] as $holding) {
    if ($holding['currency_code'] == 'USD') {
        $cash += $holding['amount']; // Add to cash
    } else {
        $holdingsValue += $holding['usd_value']; // Add USD value to holdings
    }
}

// 'Total Portfolio Value'
$total = $cash + $holdingsValue;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Portfolio | Currency Trading</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/portfolio.css">
</head>

<body class="home-page">

    <nav class="main-navbar">
        <div class="main-nav-container">
            <a href="home.php" class="main-logo">Currency Trading</a>
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
        </div>

        <div class="portfolio-summary">
            <div class="portfolio-card">
                <h2>Available Cash</h2>
                <p><?= formatMoney($cash) ?></p>
            </div>
            <div class="portfolio-card">
                <h2>Holdings Value</h2>
                <p><?= formatMoney($holdingsValue) ?></p>
	    </div>
            <div class="portfolio-card">
                <h2>Total Portfolio Value</h2>
		        <p><?= formatMoney($total) ?></p>
            </div>
        </div>

        <section class="portfolio-holdings">
            <h2>My Currency Holdings</h2>

            <div class="portfolio-table-wrapper">
                <table class="portfolio-table">
                    <thead>
                        <tr>
			    <th>Currency</th>
                            <th>Amount Owned</th>
                            <th>Exchange Rate (USD)</th>
                            <th>Current Value (USD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($portfolio['holdings'] as $holding): ?>
                            <?php
                            $usdPerUnit = 0;

			                // Calculates rate (USD value / amount)
			                if ($holding['amount'] > 0) { // If holding is an amount above 0
                                $usdPerUnit = $holding['usd_value'] / $holding['amount']; // Calculate exchange rate
                            }
                            ?>
                            <tr>
                                <td><?= $holding['currency_code'] ?></td>
                                <td><?= number_format($holding['amount'], 2) ?></td>
                                <td><?= formatRate($usdPerUnit) ?></td>
                                <td><?= formatMoney($holding['usd_value']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="portfolio-actions">
            <a href="marketplace.php" class="portfolio-button">Go to Marketplace</a>
            <a href="home.php" class="back-home-button">Back to Home</a>
        </div>

    </main>

</body>
</html>
