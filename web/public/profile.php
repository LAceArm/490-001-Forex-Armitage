<?php
require_once __DIR__ . '/../broker/session_check.php';

$username = $_SESSION['username'] ?? '';
$email = $_SESSION['email'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Profile | Forex Armitage</title>

    <link rel="stylesheet" href="assets/style.css?v=5">
    <link rel="stylesheet" href="assets/profile.css?v=7">
</head>

<body class="home-page">

    <!-- Navigation Bar -->
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

    <!-- Profile Content -->
    <main class="profile-container">

        <div class="profile-header">
            <h1>My Profile</h1>
            <p>View and edit your account information.</p>
        </div>

        <div class="profile-card">

            <h2>Account Information</h2>

            <!-- Profile Information -->
            <div id="profile-view">

                <div class="profile-info">

                    <div class="info-row">
                        <span class="info-label">Username</span>
                        <span class="info-value">
                            <?php echo htmlspecialchars($username); ?>
                        </span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value">
                            <?php echo htmlspecialchars($email); ?>
                        </span>
                    </div>

                </div>

                <div class="profile-actions">
                    <button
                        type="button"
                        class="profile-button"
                        onclick="showEditForm()">
                        Edit Profile
                    </button>

                    <a href="home.php" class="back-button">
                        Back to Home
                    </a>
                </div>

            </div>

            <!-- Edit Profile Form -->
            <form
                id="profile-edit"
                class="edit-form"
                style="display: none;"
                onsubmit="return false;">

                <div class="form-group">
                    <label for="username">Username</label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                        required>
                </div>

                <div class="profile-actions">
                    <button
                        type="submit"
                        class="profile-button">
                        Save Changes
                    </button>

                    <button
                        type="button"
                        class="cancel-button"
                        onclick="cancelEdit()">
                        Cancel
                    </button>
                </div>

                <p class="edit-note">
                    Saving account changes will be available
                    after backend integration.
                </p>

            </form>

        </div>

    </main>

    <script>
        function showEditForm() {
            document.getElementById('profile-view').style.display = 'none';
            document.getElementById('profile-edit').style.display = 'block';
        }

        function cancelEdit() {
            document.getElementById('profile-edit').reset();
            document.getElementById('profile-edit').style.display = 'none';
            document.getElementById('profile-view').style.display = 'block';
        }
    </script>

</body>
</html>
