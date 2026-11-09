
<?php
require_once __DIR__ . '/../broker/session_check.php';

$username = $currentUser['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Forex Armitage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <!-- Navigation Bar -->
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

    <!-- Home Content -->
    <main class="container py-5">

        <div class="text-center mb-5">
            <h1 class="fw-bold fs-2 mb-3">
                Welcome, <?php echo htmlspecialchars($username); ?>!
            </h1>

            <p class="text-secondary fs-6">
                Explore currencies, manage your virtual portfolio, and track your trades.
            </p>
        </div>

        <!-- Five Feature Cards -->
        <div class="row g-4 justify-content-center">

            <!-- Currency Browser -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 rounded-4">
                    <div class="card-body p-4 d-flex flex-column">
                        <h2 class="fs-5 fw-bold mb-3">Currency Browser</h2>

                        <p class="text-secondary mb-4">
                            Search currencies and view current exchange rates.
                        </p>

                        <a href="currency_browser.php" class="btn btn-primary fw-semibold mt-auto">
                            Browse Currencies
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mock Portfolio -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 rounded-4">
                    <div class="card-body p-4 d-flex flex-column">
                        <h2 class="fs-5 fw-bold mb-3">My Portfolio</h2>

                        <p class="text-secondary mb-4">
                            View your virtual cash balance and currency holdings.
                        </p>

                        <a href="portfolio.php" class="btn btn-primary fw-semibold mt-auto">
                            View Portfolio
                        </a>
                    </div>
                </div>
            </div>

            <!-- Virtual Marketplace -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 rounded-4">
                    <div class="card-body p-4 d-flex flex-column">
                        <h2 class="fs-5 fw-bold mb-3">Marketplace</h2>

                        <p class="text-secondary mb-4">
                            Trade virtual currencies using exchange rates.
                        </p>

                        <a href="marketplace.php" class="btn btn-primary fw-semibold mt-auto">
                            Start Trading
                        </a>
                    </div>
                </div>
            </div>

            <!-- Portfolio Gains -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 rounded-4">
                    <div class="card-body p-4 d-flex flex-column">
                        <h2 class="fs-5 fw-bold mb-3">Portfolio Gains</h2>

                        <p class="text-secondary mb-4">
                            Monitor your portfolio performance and gains.
                        </p>

                        <a href="gains.php" class="btn btn-primary fw-semibold mt-auto">
                            View Gains
                        </a>
                    </div>
                </div>
            </div>

            <!-- Watchlist -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 rounded-4">
                    <div class="card-body p-4 d-flex flex-column">
                        <h2 class="fs-5 fw-bold mb-3">Watchlist</h2>

                        <p class="text-secondary mb-4">
                            Track selected currencies, rate changes, and trades.
                        </p>

                        <a href="watchlist.php" class="btn btn-primary fw-semibold mt-auto">
                            View Watchlist
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </main>

</body>
</html>
