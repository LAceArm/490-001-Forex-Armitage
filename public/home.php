<?php
session_start();
if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
}
?>
<!DOCTYPE html>
<html>
<head>
        <title>Home</title>
        <link rel="stylesheet" href="assets/style.css">
</head>
<body class="home-page">
        <nav class="navbar">
                <h2>Our Website</h2>
                <div class="nav-links">
                        <a href="home.php">Home</a>
                        <a href="#">Profile</a>
                        <a href="logout.php">Logout</a>
                </div>
        </nav>
        <div class="home-content">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>!</h1>
                <p>You have successfully reached the home page.</p>
                <div class="dashboard">
                        <div class="card">
                                <h3>Profile</h3>
                                <p>View and manage your account information.</p>
                                <button>View Profile</button>
                        </div>
                        <div class="card">
                                <h3>Dashboard</h3>
                                <p>View your account dashboard.</p>
                                <button>Open Dashboard</button>
                        </div>
                </div>
        </div>
</body>
</html> 
