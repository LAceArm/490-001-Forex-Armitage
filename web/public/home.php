
<?php
session_start();


 if (empty($_SESSION['user_id'])) {
     header('Location: login.php');
     exit;
 }


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Home | Currency Trading</title>

    <link rel="stylesheet" href="assets/style.css?v=4">
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

    <!-- Home Content -->
    <main class="home-container">

        <div class="home-header">
            <h1>
                Welcome,
                <?php echo htmlspecialchars($username); ?>!
            </h1>

            <p>
                Explore currencies, manage your virtual
                portfolio, and track your trades.
            </p>
        </div>

        <!-- Five Feature Cards -->
        <div class="features-grid">

            <!-- Currency Browser -->
            <div class="feature-card">
                <h2>Currency Browser</h2>

                <p>
                    Search currencies and view
                    current exchange rates.
                </p>

                <a href="currencies.php" class="feature-button">
                    Browse Currencies
                </a>
            </div>

            <!-- Mock Portfolio -->
            <div class="feature-card">
                <h2>My Portfolio</h2>

                <p>
                    View your virtual cash balance
                    and currency holdings.
                </p>

                <a href="portfolio.php" class="feature-button">
                    View Portfolio
                </a>
            </div>

            <!-- Virtual Marketplace -->
            <div class="feature-card">
                <h2>Marketplace</h2>

                <p>
                    Trade virtual currencies
                    using exchange rates.
                </p>

                <a href="marketplace.php" class="feature-button">
                    Start Trading
                </a>
            </div>

            <!-- Portfolio Gains -->
            <div class="feature-card">
                <h2>Portfolio Gains</h2>

                <p>
                    Monitor your portfolio
                    performance and gains.
                </p>

                <a href="gains.php" class="feature-button">
                    View Gains
                </a>
            </div>

            <!-- Watchlist -->
            <div class="feature-card">
                <h2>Watchlist</h2>

                <p>
                    Track selected currencies,
                    rate changes, and trades.
                </p>

                <a href="watchlist.php" class="feature-button">
                    View Watchlist
                </a>
            </div>

        </div>

    </main>

</body>
</html>
