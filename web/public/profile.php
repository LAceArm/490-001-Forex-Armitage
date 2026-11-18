
<?php

require_once __DIR__ . '/../broker/session_check.php';

$username = $_SESSION['username'] ?? '';
$email = $_SESSION['email'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Profile | Forex Armitage</title>

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

    <!-- Profile Content -->
    <main class="container py-5">

        <div class="text-center mb-4">
            <h1 class="fw-bold fs-2 mb-3">My Profile</h1>
            <p class="text-secondary fs-6">
                View and edit your account information.
            </p>
        </div>

        <div class="card shadow-sm border-0 rounded-4 mx-auto" style="max-width: 650px;">

            <div class="card-body p-4">

                <h2 class="fs-5 fw-bold mb-4">Account Information</h2>

                <!-- Profile Information -->
                <div id="profile-view">

                    <div class="mb-4">

                        <div class="d-flex justify-content-between align-items-center border-bottom py-3 gap-3">
                            <span class="fw-semibold">Username</span>
                            <span class="text-secondary">
                                <?php echo htmlspecialchars($username); ?>
                            </span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-bottom py-3 gap-3">
                            <span class="fw-semibold">Email</span>
                            <span class="text-secondary">
                                <?php echo htmlspecialchars($email); ?>
                            </span>
                        </div>

                    </div>

                    <div class="d-flex flex-wrap gap-3">
                        <button
                            type="button"
                            class="btn btn-primary fw-semibold px-4 py-2"
                            onclick="showEditForm()">
                            Edit Profile
                        </button>

                        <a href="home.php" class="btn btn-outline-primary fw-semibold px-4 py-2">
                            Back to Home
                        </a>
                    </div>

                </div>

                <!-- Edit Profile Form -->
                <form
                    id="profile-edit"
                    class="d-none"
                    onsubmit="return false;">

                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold mb-2">
                            Username
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="username"
                            name="username"
                            value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold mb-2">
                            Email
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                            required>
                    </div>

                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <button
                            type="submit"
                            class="btn btn-primary fw-semibold px-4 py-2">
                            Save Changes
                        </button>

                        <button
                            type="button"
                            class="btn btn-outline-secondary fw-semibold px-4 py-2"
                            onclick="cancelEdit()">
                            Cancel
                        </button>
                    </div>

                    <p class="text-secondary small mt-3 mb-0">
                        Saving account changes will be available
                        after backend integration.
                    </p>

                </form>

            </div>

        </div>

    </main>

    <script>
        function showEditForm() {
            document.getElementById('profile-view').classList.add('d-none');
            document.getElementById('profile-edit').classList.remove('d-none');
        }

        function cancelEdit() {
            document.getElementById('profile-edit').reset();
            document.getElementById('profile-edit').classList.add('d-none');
            document.getElementById('profile-view').classList.remove('d-none');
        }
    </script>

</body>
</html>
