
<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$currencies = [
    'USD' => ['name' => 'US Dollar', 'rate' => null],
    'EUR' => ['name' => 'Euro', 'rate' => null],
    'GBP' => ['name' => 'British Pound', 'rate' => null],
    'JPY' => ['name' => 'Japanese Yen', 'rate' => null],
    'CNY' => ['name' => 'Chinese Yuan', 'rate' => null],
    'CHF' => ['name' => 'Swiss Franc', 'rate' => null],
    'AED' => ['name' => 'UAE Dirham', 'rate' => null]
];

$userId = (string) $_SESSION['user_id'];

if (!isset($_SESSION['watchlists'])) {
    $_SESSION['watchlists'] = [];
}

if (!isset($_SESSION['watchlists'][$userId])) {
    $_SESSION['watchlists'][$userId] = [];
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $currencyCode = $_POST['currency'] ?? '';
    $submittedToken = $_POST['csrf_token'] ?? '';

    if (!is_string($submittedToken) ||
        !hash_equals($_SESSION['csrf_token'], $submittedToken)) {

        $error = 'Invalid request. Please try again.';

    } elseif ($action === 'add') {

        if (!isset($currencies[$currencyCode])) {

            $error = 'Please select a valid currency.';

        } elseif (in_array(
            $currencyCode,
            $_SESSION['watchlists'][$userId],
            true
        )) {

            $error = 'This currency is already in your watchlist.';

        } else {

            $_SESSION['watchlists'][$userId][] = $currencyCode;
            $message = 'Currency added to your watchlist.';

        }

    } elseif ($action === 'remove') {

        if (!in_array(
            $currencyCode,
            $_SESSION['watchlists'][$userId],
            true
        )) {

            $error = 'Currency not found in your watchlist.';

        } else {

            $_SESSION['watchlists'][$userId] = array_values(
                array_filter(
                    $_SESSION['watchlists'][$userId],
                    function ($code) use ($currencyCode) {
                        return $code !== $currencyCode;
                    }
                )
            );

            $message = 'Currency removed from your watchlist.';

        }

    } else {

        $error = 'Invalid action.';

    }
}

$watchlist = $_SESSION['watchlists'][$userId];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Watchlist | Currency Trading</title>

    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/watchlist.css">
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

    <main class="watchlist-container">

        <div class="watchlist-header">

            <h1>My Watchlist</h1>

            <p>
                Track your favorite currencies and monitor exchange rates.
            </p>

        </div>

        <section class="watchlist-card">

            <h2>Add Currency</h2>

            <form
                method="POST"
                action="watchlist.php"
                class="watchlist-form"
            >

                <input
                    type="hidden"
                    name="action"
                    value="add"
                >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
                >

                <select name="currency" required>

                    <option value="">Select a currency</option>

                    <?php foreach ($currencies as $code => $currency): ?>

                        <option value="<?php echo htmlspecialchars($code); ?>">

                            <?php
                            echo htmlspecialchars(
                                $code . ' - ' . $currency['name']
                            );
                            ?>

                        </option>

                    <?php endforeach; ?>

                </select>

                <button type="submit" class="watchlist-button">
                    Add to Watchlist
                </button>

            </form>

            <?php if ($error !== ''): ?>

                <p class="watchlist-error">
                    <?php echo htmlspecialchars($error); ?>
                </p>

            <?php endif; ?>

            <?php if ($message !== ''): ?>

                <p class="watchlist-success">
                    <?php echo htmlspecialchars($message); ?>
                </p>

            <?php endif; ?>

        </section>

        <section class="watchlist-card">

            <h2>Tracked Currencies</h2>

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

                        <?php foreach ($watchlist as $code): ?>

                            <?php if (isset($currencies[$code])): ?>

                                <tr>

                                    <td>
                                        <?php echo htmlspecialchars($code); ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $currencies[$code]['name']
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo $currencies[$code]['rate'] !== null
                                            ? number_format($currencies[$code]['rate'], 4)
                                            : 'Not available';
                                        ?>
                                    </td>

                                    <td>
                                        Not available
                                    </td>

                                    <td>

                                        <form
                                            method="POST"
                                            action="watchlist.php"
                                        >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="remove"
                                            >

                                            <input
                                                type="hidden"
                                                name="currency"
                                                value="<?php echo htmlspecialchars($code); ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="remove-button"
                                            >
                                                Remove
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endif; ?>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

            <?php if (empty($watchlist)): ?>

                <p class="watchlist-message">
                    Your watchlist is empty. Add a currency to get started.
                </p>

            <?php endif; ?>

        </section>

        <div class="watchlist-actions">

            <a href="currencies.php" class="watchlist-button">
                Browse Currencies
            </a>

            <a href="home.php" class="back-home-button">
                Back to Home
            </a>

        </div>

    </main>

</body>
</html>
