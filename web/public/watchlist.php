
<?php
require_once __DIR__ . '/../broker/session_check.php';
require_once __DIR__ . '/../data/currencies.php';
require_once __DIR__ . '/../helpers/format.php';

$key = $_SESSION['session_key'];

// Handle a submitted form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $r = null;

    switch ($action) {
        case 'create': // Create watchlist
            $r = send_to_broker([
                'type' => 'create_watchlist',
                'name' => trim($_POST['name'] ?? ''),
                'session_key' => $key
            ]);
            break;

        case 'delete': // Delete a whole watchlist
            $r = send_to_broker([
                'type' => 'delete_watchlist',
                'watchlist_id' => (int)($_POST['watchlist_id'] ?? 0),
                'session_key' => $key
            ]);
            break;

        case 'add': // Add currency to watchlist
            $r = send_to_broker([
                'type' => 'add_watchlist_item',
                'watchlist_id' => (int)($_POST['watchlist_id'] ?? 0),
                'currency_code' => $_POST['currency_code'] ?? '',
                'session_key' => $key
            ]);
            break;

        case 'remove': // Remove currency from watchlist
            $r = send_to_broker([
                'type' => 'remove_watchlist_item',
                'watchlist_id' => (int)($_POST['watchlist_id'] ?? 0),
                'currency_code' => $_POST['currency_code'] ?? '',
                'session_key' => $key
            ]);
            break;
    }

    // Check for errors. If broker does not return an error, a default message is used.
    if ($r !== null && empty($r['success'])) {
        $_SESSION['flash'] = $r['error'] ?? 'That did not work, please try again';
    }

    // Averts duplicates by reloading the page
    header('Location: watchlist.php');
    die(); //A matter of personal taste. (previous code used exit()).
}

// Show the flash error
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// The user's watchlists, with each currency's latest USD price and daily change.
$reply = send_to_broker(['type' => 'get_watchlists', 'session_key' => $key]);
$watchlists = $reply['watchlists'] ?? [];

$available = [];
$ratesReply = send_to_broker(['type' => 'get_rates']);

foreach ($ratesReply['rates'] ?? [] as $row) {
    $available[] = $row['currency_code'];
}

sort($available); // Makes the list accessible
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watchlists | Forex Armitage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <!-- Navigation bar (same links as the other pages) -->
    <nav class="navbar bg-white shadow-sm py-3">
        <div class="container">
            <a href="home.php" class="navbar-brand fw-bold text-primary">Forex Armitage</a>

            <div class="d-flex gap-3 align-items-center flex-wrap">
                <a href="dashboard.php" class="text-dark text-decoration-none">Dashboard</a>
                <a href="notifications.php" class="text-dark text-decoration-none">Notifications</a>
                <a href="profile.php" class="text-dark text-decoration-none">Profile</a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <main class="container py-5">

        <div class="text-center mb-4">
            <h1>My Watchlists</h1>
            <p class="text-secondary">Create watchlists to organize and track currencies.</p>
        </div>

        <!-- An error from the last action, if there was one -->
        <?php if ($flash !== ''): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($flash) ?>
            </div>
        <?php endif; ?>

        <!-- Create a new, empty watchlist -->
        <section class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-4">
                <h2 class="fs-5 mb-3">Create New Watchlist</h2>

                <form method="POST" action="watchlist.php" class="d-flex gap-2 flex-wrap">
                    <input type="hidden" name="action" value="create">

                    <input type="text" name="name" class="form-control flex-grow-1"
                           style="width: 250px;" placeholder="Enter watchlist name"
                           maxlength="50" required>

                    <button type="submit" class="btn btn-primary">
                        Create Watchlist
                    </button>
                </form>
            </div>
        </section>

        <!-- One card per watchlist -->
        <?php foreach ($watchlists as $list): ?>
            <?php
            // Currencies already in this list, so the dropdown only offers new ones.
            $inList = array_column($list['currencies'], 'currency_code');
            ?>

            <section class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                        <h2 class="fs-5 mb-0"><?= htmlspecialchars($list['name']) ?></h2>

                        <!-- Delete the whole list (its currencies go with it) -->
                        <form method="POST" action="watchlist.php"
                              onsubmit="return confirm('Delete this watchlist?');">

                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="watchlist_id" value="<?= (int)$list['watchlist_id'] ?>">

                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                Delete Watchlist
                            </button>
                        </form>
                    </div>

                    <!-- Add a currency to this list -->
                    <form method="POST" action="watchlist.php" class="d-flex gap-2 flex-wrap mb-4">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="watchlist_id" value="<?= (int)$list['watchlist_id'] ?>">

                        <select name="currency_code" class="form-select flex-grow-1"
                                style="width: 250px;" required>
                            <option value="">Select a currency</option>

                            <?php foreach ($available as $code): ?>
                                <?php if (!in_array($code, $inList, true)): ?>
                                    <option value="<?= htmlspecialchars($code) ?>">
                                        <?= htmlspecialchars($code) ?> -
                                        <?= htmlspecialchars($currencyNames[$code] ?? $code) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" class="btn btn-primary">
                            Add Currency
                        </button>
                    </form>

                    <!-- The currencies in this list -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">

                            <thead class="table-light">
                                <tr>
                                    <th>Currency Code</th>
                                    <th>Currency Name</th>
                                    <th>Exchange Rate (USD)</th>
                                    <th>Rate Change</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($list['currencies'] as $c): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($c['currency_code']) ?></td>

                                        <td>
                                            <?= htmlspecialchars($currencyNames[$c['currency_code']] ?? $c['currency_code']) ?>
                                        </td>

                                        <td><?= formatRate($c['usd_per_unit']) ?></td>

                                        <td>
                                            <?= number_format($c['change_percent'], 2) ?>%
                                        </td>

                                        <td>
                                            <form method="POST" action="watchlist.php">
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="watchlist_id" value="<?= (int)$list['watchlist_id'] ?>">
                                                <input type="hidden" name="currency_code" value="<?= htmlspecialchars($c['currency_code']) ?>">

                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    Remove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>

                        </table>
                    </div>

                    <?php if (!$list['currencies']): ?>
                        <p class="text-secondary">This watchlist is empty. Add a currency to get started.</p>
                    <?php endif; ?>

                </div>
            </section>

        <?php endforeach; ?>

        <?php if (!$watchlists): ?>
            <p class="text-center text-secondary">
                No watchlists created yet. Create one to get started.
            </p>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="currency_browser.php" class="btn btn-primary me-2">
                Browse Currencies
            </a>

            <a href="home.php" class="btn btn-outline-primary">
                Back to Home
            </a>
        </div>

    </main>

</body>
</html>
