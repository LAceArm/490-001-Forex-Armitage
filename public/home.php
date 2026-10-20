
<?php
/*
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="home-page">

    <!-- Navigation Bar -->
    <nav class="navbar">
        <h2>Our Website</h2>

        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <!-- Home Content -->
    <div class="home-content">

        <h1>
            Welcome,
            <?php
                echo htmlspecialchars(
                    $_SESSION['username'] ?? 'TestUser'
                );
            ?>!
        </h1>

        <p>You have successfully reached the home page.</p>

        <!-- Dashboard -->
        <div class="dashboard">
            <div class="card">
                <h3>Dashboard</h3>
                <p>View your account dashboard.</p>

                <button type="button"
                    onclick="window.location.href='dashboard.php'">
                    Open Dashboard
                </button>
            </div>
        </div>

    </div>

</body>
</html>
