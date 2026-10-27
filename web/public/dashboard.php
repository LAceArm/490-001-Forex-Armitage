
<?php
session_start();

// Uncomment when testing with the login system.
// if (empty($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit;
// }

$username = $_SESSION['username'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard | Currency Trading</title>

    <link rel="stylesheet" href="assets/dashboard.css?v=7">
    <link rel="stylesheet" href="assets/style.css?v=5">
</head>

<body class="home-page">

    <!-- Navigation Bar -->
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

    <!-- Dashboard Content -->
    <main class="dashboard-container">

        <div class="dashboard-header">

            <h1>Portfolio Dashboard</h1>

            <p>
                Welcome,
                <?php echo htmlspecialchars($username); ?>!
            </p>

            <p>
                View your portfolio overview and recent activity.
            </p>

        </div>

        <!-- Portfolio Statistics -->
        <div class="stats-grid">

            <div class="stat-card">
                <h2>Total Portfolio Value</h2>
                <p id="portfolio-value">--</p>
            </div>

            <div class="stat-card">
                <h2>Available Cash</h2>
                <p id="cash-balance">--</p>
            </div>

            <div class="stat-card">
                <h2>Total Gains</h2>
                <p id="total-gains">--</p>
            </div>

            <div class="stat-card">
                <h2>Currencies Held</h2>
                <p id="currencies-held">--</p>
            </div>

        </div>

        <!-- Recent Trades Section -->
        <section class="activity-section">

            <h2>Recent Trades</h2>

            <div class="table-wrapper">

                <table class="activity-table">

                    <thead>
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

        </section>

        <!-- Back to Home -->
        <div class="dashboard-footer">

            <a href="home.php" class="back-button">
                Back to Home
            </a>

        </div>

    </main>

</body>
</html>
