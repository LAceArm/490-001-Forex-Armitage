<?php
require_once __DIR__ . '/../broker/session_check.php'; // Checks if session key is valid
require_once __DIR__ . '/../helpers/format.php';

// Asks DB side for user's holdings
$portfolioReply = send_to_broker([
    'type' => 'get_portfolio',
    'session_key' => $_SESSION['session_key']
]);

// 'Available Cash' and 'Holdings Value', respectively
$cash = 0;
$holdingsValue = 0;

// Go through each holding
foreach ($portfolioReply['holdings'] as $holding) {
    if ($holding['currency_code'] == 'USD') {
        $cash += $holding['amount']; // Add to cash
    } else {
        $holdingsValue += $holding['usd_value']; // Add USD value to holdings
    }
}

// 'Total Portfolio Value'
$portfolioValue = $cash + $holdingsValue;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Portfolio | Forex Armitage</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark py-3">
        <div class="container-fluid px-4">

            <a href="home.php" class="navbar-brand fw-bold">
                Forex Armitage
            </a>

            <div class="d-flex flex-wrap align-items-center gap-3">
                <a href="dashboard.php" class="text-white text-decoration-none">Dashboard</a>
                <a href="notifications.php" class="text-white text-decoration-none">Notifications</a>
                <a href="profile.php" class="text-white text-decoration-none">Profile</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>

        </div>
    </nav>

    <main class="container py-5">

        <div class="mb-4">
            <h1 class="fw-bold">My Portfolio</h1>
        </div>

        <div class="row g-4 mb-4">

            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-4">
                        <h2 class="h5">Available Cash</h2>
                        <p class="fs-3 fw-bold mb-0">
                            <?= formatMoney($cash) ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-4">
                        <h2 class="h5">Holdings Value</h2>
                        <p class="fs-3 fw-bold mb-0">
                            <?= formatMoney($holdingsValue) ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-4">
                        <h2 class="h5">Total Portfolio Value</h2>
                        <p class="fs-3 fw-bold mb-0">
                            <?= formatMoney($portfolioValue) ?>
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <section class="card shadow-sm mb-4">
            <div class="card-body p-4">

                <h2 class="h4 fw-bold mb-4">My Currency Holdings</h2>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Currency</th>
                                <th>Amount Owned</th>
                                <th>Exchange Rate (USD)</th>
                                <th>Current Value (USD)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($portfolioReply['holdings'] as $holding): ?>
                                <?php
                                $usdPerUnit = 0;

                                // Rate = USD value / amount
                                if ($holding['amount'] > 0) {
                                    $usdPerUnit = $holding['usd_value'] / $holding['amount'];
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

            </div>
        </section>

        <div class="d-flex flex-wrap gap-3">
            <a href="marketplace.php" class="btn btn-primary">
                Go to Marketplace
            </a>

            <a href="home.php" class="btn btn-outline-secondary">
                Back to Home</a>
        </div>

    </main>

</body>
</html>