
<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$notifications = [];
$unreadCount = 0;

foreach ($notifications as $notification) {
    if (empty($notification['is_read'])) {
        $unreadCount++;
    }
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = 'Notification actions are not available yet.';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Notifications | Forex Armitage</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

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

    <main class="container py-5">

        <div class="text-center mb-4">

            <h1 class="fw-bold fs-2 mb-3">Notifications</h1>

            <p class="text-secondary fs-6 mb-3">
                View your trading updates, currency alerts, and account activity.
            </p>

            <span class="badge bg-primary rounded-pill px-3 py-2">
                <?php echo $unreadCount; ?> Unread
            </span>

        </div>

        <section class="card shadow-sm border-0 rounded-4 mb-4">

            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

                    <h2 class="fs-5 fw-bold mb-0">All Notifications</h2>

                    <form method="POST" action="notifications.php">
                        <input type="hidden" name="action" value="mark_all_read">

                        <button
                            type="submit"
                            class="btn btn-primary btn-sm fw-semibold"
                            <?php echo empty($notifications) ? 'disabled' : ''; ?>
                        >
                            Mark All as Read
                        </button>
                    </form>

                </div>

                <?php if ($message !== ''): ?>

                    <div class="alert alert-info">
                        <?php echo htmlspecialchars($message); ?>
                    </div>

                <?php endif; ?>

                <?php if (empty($notifications)): ?>

                    <div class="text-center py-5">

                        <h3 class="fs-5 fw-bold mb-3">No Notifications Yet</h3>

                        <p class="text-secondary mb-0">
                            Your trade confirmations, currency alerts,
                            and account updates will appear here.
                        </p>

                    </div>

                <?php else: ?>

                    <div class="list-group">

                        <?php foreach ($notifications as $notification): ?>

                            <div class="list-group-item p-4 <?php
                                echo empty($notification['is_read'])
                                    ? 'bg-primary-subtle'
                                    : '';
                            ?>">

                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-2">

                                    <h3 class="fs-6 fw-bold mb-0">
                                        <?php echo htmlspecialchars($notification['title'] ?? ''); ?>
                                    </h3>

                                    <span class="text-secondary small">
                                        <?php echo htmlspecialchars($notification['date'] ?? ''); ?>
                                    </span>

                                </div>

                                <p class="text-secondary mb-3">
                                    <?php echo htmlspecialchars($notification['message'] ?? ''); ?>
                                </p>

                                <span class="badge bg-secondary mb-3">
                                    <?php echo htmlspecialchars($notification['type'] ?? 'General'); ?>
                                </span>

                                <div class="d-flex flex-wrap gap-2">

                                    <?php if (empty($notification['is_read'])): ?>

                                        <form method="POST" action="notifications.php">

                                            <input type="hidden" name="action" value="mark_read">

                                            <input
                                                type="hidden"
                                                name="notification_id"
                                                value="<?php echo htmlspecialchars((string)($notification['id'] ?? '')); ?>"
                                            >

                                            <button type="submit" class="btn btn-outline-primary btn-sm fw-semibold">
                                                Mark as Read
                                            </button>

                                        </form>

                                    <?php endif; ?>

                                    <form method="POST" action="notifications.php">

                                        <input type="hidden" name="action" value="delete">

                                        <input
                                            type="hidden"
                                            name="notification_id"
                                            value="<?php echo htmlspecialchars((string)($notification['id'] ?? '')); ?>"
                                        >

                                        <button type="submit" class="btn btn-outline-danger btn-sm fw-semibold">
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

        </section>

        <div class="text-center mt-4">
            <a href="home.php" class="btn btn-outline-primary fw-semibold px-4 py-2">
                Back to Home
            </a>
        </div>

    </main>

</body>
</html>
