
<?php
require_once __DIR__ . '/../broker/session_check.php'; // Checks if session key is valid
require_once __DIR__ . '/../helpers/format.php';

// Asks DB side for user's holdings
$portfolioReply = sendToBackend([
    'type'        => 'get_portfolio',
    'session_key' => $_SESSION['session_key']
]);

// Asks DB side for user's trade history
$tradesReply = sendToBackend([
    'type'        => 'get_trades',
    'session_key' => $_SESSION['session_key']
]);

$gainRows = []; // One row per currency owned
$portfolioValue = 0;

foreach ($portfolioReply['holdings'] as $holding) {
    $portfolioValue += $holding['usd_value'];

    // Sold-out currencies have nothing to measure
    if ($holding['currency_code'] == 'USD' || $holding['amount'] == 0) {
        continue;
    }

    $usdSpent = 0;
    $unitsBought = 0;

    foreach ($tradesReply['trades'] as $trade) {
        if ($trade['from_code'] == 'USD' && $trade['to_code'] == $holding['currency_code']) {
            $usdSpent += $trade['from_amount'];
            $unitsBought += $trade['to_amount'];
        }
    }

    $usdPerUnit = $holding['usd_value'] / $holding['amount']; // USD value of 1 unit

    // Average USD paid per unit (USD itself is always 1)
    $purchaseUsdPerUnit = null;
    $gain = null;

    if ($unitsBought > 0) {
        $purchaseUsdPerUnit = $usdSpent / $unitsBought;
        $gain = $holding['amount'] * ($usdPerUnit - $purchaseUsdPerUnit);
    }

    $gainRows[] = [
        'currency_code'         => $holding['currency_code'],
        'amount'                => $holding['amount'],
        'purchase_usd_per_unit' => $purchaseUsdPerUnit,
        'usd_per_unit'          => $usdPerUnit,
        'gain'                  => $gain
    ];
}

// Overall performance
$startingCash = $portfolioReply['starting_cash'];
$totalGain = $portfolioValue - $startingCash;
$performancePercent = $startingCash != 0
    ? $totalGain / $startingCash * 100
    : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Gains | Forex Armitage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg bg-white shadow-sm py-3">
        <div class="container">

            <a href="home.php" class="navbar-brand fw-bold fs-4 text-primary">
                Forex Armitage
            </a>

            <div class="d-flex flex-wrap align-items-center gap-3">
                <a href="dashboard.php" class="text-decoration-none text-dark fw-semibold">Dashboard</a>
                <a href="notifications.php" class="text-decoration-none text-dark fw-semibold">Notifications</a>
                <a href="profile.php" class="text-decoration-none text-dark fw-semibold">Profile</a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm fw-semibold">Logout</a>
            </div>

        </div>
    </nav>

    <main class="container py-5">

        <div class="text-center mb-4">
            <h1 class="fw-bold fs-2 mb-3">Portfolio Gains</h1>
            <p class="text-secondary fs-6">
                Track the performance of your virtual currency holdings.
            </p>
        </div>

        <div class="row g-4 mb-4">

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body p-4 text-center">
                        <h2 class="fs-6 fw-semibold text-secondary mb-3">
                            Initial Portfolio Value
                        </h2>
                        <p class="fs-3 fw-bold text-primary mb-0">
                            <?= formatMoney($startingCash) ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body p-4 text-center">
                        <h2 class="fs-6 fw-semibold text-secondary mb-3">
                            Current Portfolio Value
                        </h2>
                        <p class="fs-3 fw-bold text-primary mb-0">
                            <?= formatMoney($portfolioValue) ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body p-4 text-center">
                        <h2 class="fs-6 fw-semibold text-secondary mb-3">
                            Total Gain / Loss
                        </h2>
                        <p class="fs-3 fw-bold mb-0 <?= $totalGain >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= formatMoney($totalGain) ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body p-4 text-center">
                        <h2 class="fs-6 fw-semibold text-secondary mb-3">
                            Performance
                        </h2>
                        <p class="fs-3 fw-bold mb-0 <?= $performancePercent === null ? 'text-secondary' : ($performancePercent >= 0 ? 'text-success' : 'text-danger') ?>">
                            <?= $performancePercent !== null ? number_format($performancePercent, 2) . '%' : 'Not available' ?>
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <section class="card shadow-sm border-0 rounded-4 mb-4">

            <div class="card-body p-4">

                <h2 class="fs-5 fw-bold mb-3">Currency Performance</h2>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">

                        <thead class="table-light">
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
                                    <td>
                                        <?= htmlspecialchars($gainRow['currency_code']) ?>
                                    </td>

                                    <td>
                                        <?= number_format($gainRow['amount'], 2) ?>
                                    </td>

                                    <td>
                                        <?= formatRate($gainRow['purchase_usd_per_unit']) ?>
                                    </td>

                                    <td>
                                        <?= formatRate($gainRow['usd_per_unit']) ?>
                                    </td>

                                    <td class="fw-semibold <?= $gainRow['gain'] === null ? 'text-secondary' : ($gainRow['gain'] >= 0 ? 'text-success' : 'text-danger') ?>">
                                        <?= formatMoney($gainRow['gain']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>

            </div>

        </section>

        <div class="d-flex justify-content-center flex-wrap gap-3 mt-4">
            <a href="portfolio.php" class="btn btn-primary fw-semibold px-4 py-2">
                View Portfolio
            </a>

            <a href="home.php" class="btn btn-outline-primary fw-semibold px-4 py-2">
                Back to Home
            </a>
        </div>

    </main>

</body>
</html>
