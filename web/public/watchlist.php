<?php
require_once __DIR__ . '/../broker/session_check.php';

$key = $_SESSION['session_key'];

// Handle a submitted form. Each action maps to one broker request; the user is
// found from the session key, so no user id is sent.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $r      = null;

    switch($action) {
    	case 'create': // Create watchlist
            $r = send_to_broker([
            'type'        => 'create_watchlist',
            'name'        => trim($_POST['name'] ?? ''),
            'session_key' => $key,
            ]);
	        break;
	    case 'delete': // Delete a whole watchlist
            $r = send_to_broker([
                'type'         => 'delete_watchlist',
                'watchlist_id' => (int)($_POST['watchlist_id'] ?? 0),
                'session_key'  => $key,
            ]);
            break;
	    case 'add': //Add currency to watchlist
            $r = send_to_broker([
                'type'         => 'add_watchlist_item',
                'watchlist_id' => (int)($_POST['watchlist_id'] ?? 0),
                'code'         => $_POST['code'] ?? '',
                'session_key'  => $key,
            ]);
            break;
	    case 'remove': //Remove currency from watchlist
            $r = send_to_broker([
                'type'         => 'remove_watchlist_item',
                'watchlist_id' => (int)($_POST['watchlist_id'] ?? 0),
                'code'         => $_POST['code'] ?? '',
                'session_key'  => $key,
            ]);
	        break;
    }

    // Check for errors. If broker does not return an error, a default message is used.
	if ($r !== null && empty($r['success'])) {
        $_SESSION['flash']=  $r['error'] ?? 'That did not work, please try again';
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

$available = [];
$ratesReply = send_to_broker(['type' => 'get_rates']);
foreach ($ratesReply['rates'] ?? [] as $row) {
	$available[] = $row['currency_code'];
}

sort($available); // Makes the list accessible
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watchlists | Forex Armitage</title>
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
            <p class="watchlist-message"><?= htmlspecialchars($flash) ?></p>
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
                    <h2><?= $list['name'] ?></h2>

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
                                <option value="<?= $code ?>">
                                   <?= $code ?> - <?= $names[$code] ?>
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
				    <td><?=$c['code'] ?></td>
				    <td> <?=$names[$c['code']] ?>
                                    <td><?= number_format($c['price'], $c['price'] < 0.01 ? 6 : 4) ?></td>
                                    <td>
					<?= $c['change'].number_format($c['change'],2).'%' ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="watchlist.php">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="watchlist_id" value="<?= (int)$list['id'] ?>">
                                            <input type="hidden" name="code" value="<?= $c['code'] ?>">
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
            <a href="currency_browser.php" class="watchlist-button">Browse Currencies</a>
            <a href="home.php" class="back-home-button">Back to Home</a>
        </div>

    </main>

</body>
</html>
