<?php
// ---------------------------------------------------------------------------
// watchlist.php: the user's named watchlists and the currencies in them.
// Every action (create, delete, add, remove) is a form that posts to this page,
// calls the broker, then redirects so a refresh can't repeat it.
// ---------------------------------------------------------------------------

// Only logged-in users get past this line. It checks the session key with the DB.
require_once __DIR__ . '/../broker/session_check.php';

$key = $_SESSION['session_key'];

// Escape text for HTML, and format a USD price (more decimals for tiny prices like JPY).
$e     = fn($v) => htmlspecialchars((string)$v);
$price = fn($p) => $p === null ? 'Not available' : number_format($p, $p < 0.01 ? 6 : 4);

// Handle a submitted form. Each action maps to one broker request; the user is
// found from the session key, so no user id is sent.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    $requests = [
        'create' => ['type' => 'create_watchlist',      'name' => trim($_POST['name'] ?? '')],
        'delete' => ['type' => 'delete_watchlist',      'watchlist_id' => (int)($_POST['watchlist_id'] ?? 0)],
        'add'    => ['type' => 'add_watchlist_item',    'watchlist_id' => (int)($_POST['watchlist_id'] ?? 0), 'code' => $_POST['code'] ?? ''],
        'remove' => ['type' => 'remove_watchlist_item', 'watchlist_id' => (int)($_POST['watchlist_id'] ?? 0), 'code' => $_POST['code'] ?? ''],
    ];

    if (isset($requests[$action])) {
        $r = send_to_broker($requests[$action] + ['session_key' => $key]);
        // One-time message for the next page load, only if something went wrong.
        if (empty($r['success'])) {
            $_SESSION['flash'] = $r['error'] ?? 'That did not work. Please try again.';
        }
    }

    // Redirect so refreshing the page can't repeat the action.
    header('Location: watchlist.php');
    exit;
}

// Show the last error once, then clear it.
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// The user's watchlists, with each currency's latest USD price and daily change.
$reply      = send_to_broker(['type' => 'get_watchlists', 'session_key' => $key]);
$watchlists = $reply['watchlists'] ?? [];

// Readable names. The DB currently stores each code as its own name, so this
// map supplies them; anything not listed falls back to showing its code.
$names = [
    'AED' => 'United Arab Emirates Dirham',
    'CAD' => 'Canadian Dollar',
    'CHF' => 'Swiss Franc',
    'CNY' => 'Chinese Yuan',
    'EUR' => 'Euro',
    'GBP' => 'British Pound',
    'JPY' => 'Japanese Yen',
    'USD' => 'US Dollar',
];

// Currencies that can be added: the ones that have a rate, in alphabetical order.
$available = [];
$ratesReply = send_to_broker(['type' => 'get_rates']);
foreach ($ratesReply['rates'] ?? [] as $row) {
    if ($row['base_currency'] === 'USD') {
        $available[] = $row['quote_currency'];
    }
}
sort($available);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Watchlists | Forex Armitage</title>

    <!-- Shared site styles, then styles for this page -->
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/watchlist.css">
</head>

<body class="home-page">

    <!-- Navigation bar (same links as the other pages) -->
    <nav class="main-navbar">
        <div class="main-nav-container">
            <a href="home.php" class="main-logo">Forex Armitage</a>

            <div class="main-nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="notifications.php">Notifications</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <main class="watchlist-container">

        <div class="watchlist-header">
            <h1>My Watchlists</h1>
            <p>Create watchlists to organize and track currencies.</p>
        </div>

        <!-- An error from the last action, if there was one -->
        <?php if ($flash !== ''): ?>
            <p class="watchlist-message"><?= $e($flash) ?></p>
        <?php endif; ?>

        <!-- Create a new, empty watchlist -->
        <section class="watchlist-card">
            <h2>Create New Watchlist</h2>

            <form method="POST" action="watchlist.php" class="watchlist-form">
                <input type="hidden" name="action" value="create">
                <input type="text" name="name" placeholder="Enter watchlist name" maxlength="50" required>
                <button type="submit" class="watchlist-button">Create Watchlist</button>
            </form>
        </section>

        <!-- One card per watchlist -->
        <?php foreach ($watchlists as $list): ?>
            <?php
            // Currencies already in this list, so the dropdown only offers new ones.
            $inList = array_column($list['currencies'], 'code');
            ?>
            <section class="watchlist-card">

                <div class="watchlist-section-header">
                    <h2><?= $e($list['name']) ?></h2>

                    <!-- Delete the whole list (its currencies go with it) -->
                    <form method="POST" action="watchlist.php"
                          onsubmit="return confirm('Delete this watchlist?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="watchlist_id" value="<?= (int)$list['id'] ?>">
                        <button type="submit" class="delete-watchlist-button">Delete Watchlist</button>
                    </form>
                </div>

                <!-- Add a currency to this list -->
                <form method="POST" action="watchlist.php" class="watchlist-form">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="watchlist_id" value="<?= (int)$list['id'] ?>">

                    <select name="code" required>
                        <option value="">Select a currency</option>
                        <?php foreach ($available as $code): ?>
                            <?php if (!in_array($code, $inList, true)): ?>
                                <option value="<?= $e($code) ?>">
                                    <?= $e($code . ' - ' . ($names[$code] ?? $code)) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="watchlist-button">Add Currency</button>
                </form>

                <!-- The currencies in this list -->
                <div class="watchlist-table-wrapper">
                    <table class="watchlist-table">

                        <thead>
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
                                    <td><?= $e($c['code']) ?></td>
                                    <td><?= $e($names[$c['code']] ?? $c['code']) ?></td>
                                    <td><?= $price($c['price']) ?></td>
                                    <td>
                                        <?= $c['change'] === null
                                            ? 'Not available'
                                            : ($c['change'] > 0 ? '+' : '') . number_format($c['change'], 2) . '%' ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="watchlist.php">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="watchlist_id" value="<?= (int)$list['id'] ?>">
                                            <input type="hidden" name="code" value="<?= $e($c['code']) ?>">
                                            <button type="submit" class="remove-button">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>

                <?php if (!$list['currencies']): ?>
                    <p class="watchlist-message">This watchlist is empty. Add a currency to get started.</p>
                <?php endif; ?>

            </section>
        <?php endforeach; ?>

        <?php if (!$watchlists): ?>
            <p class="watchlist-message">No watchlists created yet. Create one to get started.</p>
        <?php endif; ?>

        <div class="watchlist-actions">
            <a href="currencies.php" class="watchlist-button">Browse Currencies</a>
            <a href="home.php" class="back-home-button">Back to Home</a>
        </div>

    </main>

</body>
</html>