
<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$holdings = [];

$initialValue = null;
$currentValue = null;
$totalGain = null;
$gainPercentage = null;

if (!empty($holdings)) {
    $initialValue = 0;
    $currentValue = 0;

    foreach ($holdings as $holding) {
        $amount = (float)($holding['amount'] ?? 0);
        $purchaseRate = (float)($holding['purchase_rate'] ?? 0);
        $currentRate = (float)($holding['current_rate'] ?? 0);

        $initialValue += $amount * $purchaseRate;
        $currentValue += $amount * $currentRate;
    }

    $totalGain = $currentValue - $initialValue;

    if ($initialValue > 0) {
        $gainPercentage = ($totalGain / $initialValue) * 100;
    }
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

            <a href="home.php" class="main-logo">
                Forex Armitage
            </a>

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
                <p>
                    <?php
                    echo $initialValue !== null
                        ? '$' . number_format($initialValue, 2)
                        : 'Not available';
                    ?>
                </p>
            </div>

            <div class="gains-card">
                <h2>Current Holdings Value</h2>
                <p>
                    <?php
                    echo $currentValue !== null
                        ? '$' . number_format($currentValue, 2)
                        : 'Not available';
                    ?>
                </p>
            </div>

            <div class="gains-card">
                <h2>Total Gain / Loss</h2>

                <p class="<?php
                    echo $totalGain === null
                        ? ''
                        : ($totalGain >= 0 ? 'positive' : 'negative');
                ?>">
                    <?php
                    echo $totalGain !== null
                        ? '$' . number_format($totalGain, 2)
                        : 'Not available';
                    ?>
                </p>
            </div>

            <div class="gains-card">
                <h2>Performance</h2>

                <p class="<?php
                    echo $gainPercentage === null
                        ? ''
                        : ($gainPercentage >= 0 ? 'positive' : 'negative');
                ?>">
                    <?php
                    echo $gainPercentage !== null
                        ? number_format($gainPercentage, 2) . '%'
                        : 'Not available';
                    ?>
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
                            <th>Purchase Rate</th>
                            <th>Current Rate</th>
                            <th>Gain / Loss</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($holdings as $holding): ?>

                            <?php
                            $amount = (float)($holding['amount'] ?? 0);
                            $purchaseRate = (float)($holding['purchase_rate'] ?? 0);
                            $currentRate = (float)($holding['current_rate'] ?? 0);
                            $gain = $amount * ($currentRate - $purchaseRate);
                            ?>

                            <tr>

                                <td>
                                    <?php echo htmlspecialchars($holding['code'] ?? ''); ?>
                                </td>

                                <td>
                                    <?php echo number_format($amount, 2); ?>
                                </td>

                                <td>
                                    <?php echo number_format($purchaseRate, 4); ?>
                                </td>

                                <td>
                                    <?php echo number_format($currentRate, 4); ?>
                                </td>

                                <td class="<?php echo $gain >= 0 ? 'positive' : 'negative'; ?>">
                                    <?php echo '$' . number_format($gain, 2); ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

            <?php if (empty($holdings)): ?>

                <p class="gains-message">
                    Portfolio performance information is not available yet.
                </p>

            <?php endif; ?>

        </section>

        <div class="gains-actions">

            <a href="portfolio.php" class="gains-button">
                View Portfolio
            </a>

            <a href="home.php" class="gains-back-button">
                Back to Home
            </a>

        </div>

    </main>

</body>
</html>
