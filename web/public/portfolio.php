
<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'] ?? '';
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

    <main class="portfolio-container">

        <div class="portfolio-header">

            <h1>My Portfolio</h1>

            <p>
                View your virtual cash balance,
                currency holdings, and total portfolio value.
            </p>

        </div>

        <div class="portfolio-summary">

            <div class="portfolio-card">
                <h2>Available Cash</h2>
                <p>Not available</p>
            </div>

            <div class="portfolio-card">
                <h2>Holdings Value</h2>
                <p>Not available</p>
            </div>

            <div class="portfolio-card">
                <h2>Total Portfolio Value</h2>
                <p>Not available</p>
            </div>

        </div>

        <section class="portfolio-holdings">

            <h2>My Currency Holdings</h2>

            <div class="portfolio-table-wrapper">

                <table class="portfolio-table">

                    <thead>
                        <tr>
                            <th>Currency Code</th>
                            <th>Currency Name</th>
                            <th>Amount Owned</th>
                            <th>Exchange Rate</th>
                            <th>Current Value</th>
                        </tr>
                    </thead>

                    <tbody>
                    </tbody>

                </table>

            </div>

            <p class="portfolio-message">
                Portfolio information is not available yet.
            </p>

        </section>

        <div class="portfolio-actions">

            <a href="marketplace.php" class="portfolio-button">
                Go to Marketplace
            </a>

            <a href="home.php" class="back-home-button">
                Back to Home
            </a>

        </div>

    </main>

</body>
</html>
