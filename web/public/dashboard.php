
<?php
require_once __DIR__ . '/../broker/session_check.php';

$username = $currentUser['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Forex Armitage</title>
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

    <!-- Dashboard Content -->
    <main class="container py-5">

        <div class="text-center mb-4">
            <h1 class="fw-bold fs-2 mb-3">Portfolio Dashboard</h1>
            <p class="text-secondary mb-2">
                Welcome, <?php echo htmlspecialchars($username); ?>!
            </p>
            <p class="text-secondary mb-0">
                View your portfolio overview and recent activity.
            </p>
        </div>

        <!-- Portfolio Statistics -->
        <div class="row g-4 mb-4">

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body p-4 text-center">
                        <h2 class="fs-6 fw-semibold text-secondary mb-3">
                            Total Portfolio Value
                        </h2>
                        <p id="portfolio-value" class="fs-3 fw-bold text-primary mb-0">--</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body p-4 text-center">
                        <h2 class="fs-6 fw-semibold text-secondary mb-3">
                            Available Cash
                        </h2>
                        <p id="cash-balance" class="fs-3 fw-bold text-primary mb-0">--</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body p-4 text-center">
                        <h2 class="fs-6 fw-semibold text-secondary mb-3">
                            Total Gains
                        </h2>
                        <p id="total-gains" class="fs-3 fw-bold text-primary mb-0">--</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body p-4 text-center">
                        <h2 class="fs-6 fw-semibold text-secondary mb-3">
                            Currencies Held
                        </h2>
                        <p id="currencies-held" class="fs-3 fw-bold text-primary mb-0">--</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Recent Trades Section -->
        <section class="card shadow-sm border-0 rounded-4 mb-4">

            <div class="card-body p-4">

                <h2 class="fs-5 fw-bold mb-3">Recent Trades</h2>

                <div class="table-responsive">

                    <table class="table table-hover table-bordered align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Trade</th>
                                <th>Amount</th>
                            </tr>
                        </thead>

                        <tbody id="recent-trades">
                            <!-- Trades will be loaded from backend -->
                        </tbody>

                    </table>

                </div>

            </div>

        </section>

        <!-- Back to Home -->
        <div class="text-center mt-4">
            <a href="home.php" class="btn btn-outline-primary fw-semibold px-4 py-2">
                Back to Home
            </a>
        </div>

    </main>

</body>
</html>
