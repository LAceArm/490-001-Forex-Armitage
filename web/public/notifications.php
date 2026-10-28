
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

    <title>Notifications | Currency Trading</title>

    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/notifications.css">
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

    <main class="notifications-container">

        <div class="notifications-header">

            <div>
                <h1>Notifications</h1>
                <p>View your trading updates, currency alerts, and account activity.</p>
            </div>

            <div class="notifications-count">
                <?php echo $unreadCount; ?> Unread
            </div>

        </div>

        <section class="notifications-card">

            <div class="notifications-toolbar">

                <h2>All Notifications</h2>

                <form method="POST" action="notifications.php">
                    <input type="hidden" name="action" value="mark_all_read">

                    <button
                        type="submit"
                        class="mark-all-button"
                        <?php echo empty($notifications) ? 'disabled' : ''; ?>
                    >
                        Mark All as Read
                    </button>
                </form>

            </div>

            <?php if ($message !== ''): ?>

                <p class="notifications-message">
                    <?php echo htmlspecialchars($message); ?>
                </p>

            <?php endif; ?>

            <?php if (empty($notifications)): ?>

                <div class="notifications-empty">
                    <h3>No Notifications Yet</h3>
                    <p>
                        Your trade confirmations, currency alerts,
                        and account updates will appear here.
                    </p>
                </div>

            <?php else: ?>

                <div class="notifications-list">

                    <?php foreach ($notifications as $notification): ?>

                        <div class="notification-item <?php
                            echo empty($notification['is_read'])
                                ? 'notification-unread'
                                : '';
                        ?>">

                            <div class="notification-content">

                                <div class="notification-top">

                                    <h3>
                                        <?php
                                        echo htmlspecialchars(
                                            $notification['title'] ?? ''
                                        );
                                        ?>
                                    </h3>

                                    <span class="notification-date">
                                        <?php
                                        echo htmlspecialchars(
                                            $notification['date'] ?? ''
                                        );
                                        ?>
                                    </span>

                                </div>

                                <p>
                                    <?php
                                    echo htmlspecialchars(
                                        $notification['message'] ?? ''
                                    );
                                    ?>
                                </p>

                                <span class="notification-type">
                                    <?php
                                    echo htmlspecialchars(
                                        $notification['type'] ?? 'General'
                                    );
                                    ?>
                                </span>

                            </div>

                            <div class="notification-actions">

                                <?php if (empty($notification['is_read'])): ?>

                                    <form method="POST" action="notifications.php">

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="mark_read"
                                        >

                                        <input
                                            type="hidden"
                                            name="notification_id"
                                            value="<?php
                                                echo htmlspecialchars(
                                                    (string)($notification['id'] ?? '')
                                                );
                                            ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="mark-read-button"
                                        >
                                            Mark as Read
                                        </button>

                                    </form>

                                <?php endif; ?>

                                <form method="POST" action="notifications.php">

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="delete"
                                    >

                                    <input
                                        type="hidden"
                                        name="notification_id"
                                        value="<?php
                                            echo htmlspecialchars(
                                                (string)($notification['id'] ?? '')
                                            );
                                        ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="delete-button"
                                    >
                                        Delete
                                    </button>

                                </form>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

        <div class="notifications-footer">

            <a href="home.php" class="back-home-button">
                Back to Home
            </a>

        </div>

    </main>

</body>
</html>
