<?php
// ---------------------------------------------------------------------------
// currencies.php: browse and search the currencies the app tracks.
// ---------------------------------------------------------------------------

// Only logged-in users get past this line. It checks the session key with the DB.
require_once __DIR__ . '/../broker/session_check.php';

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

// Escape text for HTML.
$e = fn($v) => htmlspecialchars((string)$v);

// Code => name and USD price of one unit. The DB stores rates as units per
// 1 USD, so the price is 1 / rate (the same number the Marketplace shows).
$currencies = [];
$reply = send_to_broker(['type' => 'get_rates']);
foreach ($reply['rates'] ?? [] as $row) {
    if ($row['base_currency'] === 'USD' && (float)$row['rate'] > 0) {
        $code  = $row['quote_currency'];
        $price = 1 / (float)$row['rate'];
        $currencies[$code] = [
            'name' => $names[$code] ?? $code,
            // More decimals for tiny rates like JPY so they don't show as 0.0063
            'rate' => number_format($price, $price < 0.01 ? 6 : 4),
        ];
    }
}
ksort($currencies);   // alphabetical by code
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Currency Browser | Currency Trading</title>

    <!-- Shared site styles, then styles for this page -->
    <link rel="stylesheet" href="assets/style.css?v=5">
    <link rel="stylesheet" href="assets/currencies.css?v=1">
</head>

<body class="home-page">

    <!-- Navigation bar (same links as the other pages) -->
    <nav class="main-navbar">
        <div class="main-nav-container">
            <a href="home.php" class="main-logo">Currency Trading</a>

            <div class="main-nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="notifications.php">Notifications</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <main class="currencies-container">

        <div class="currencies-header">
            <h1>Currency Browser</h1>
            <p>Browse currencies, search by name or code, and view exchange rate information.</p>
        </div>

        <!-- Search box: filters the table below as you type -->
        <div class="currency-search-section">
            <label for="currency-search">Search Currencies</label>
            <input type="text" id="currency-search"
                   placeholder="Search by currency name or code...">
        </div>

        <!-- Table of currencies. Each row carries its details as data-* attributes
             so the details panel and the search can read them without a request. -->
        <section class="currency-table-section">
            <h2>Available Currencies</h2>

            <div class="currency-table-wrapper">
                <table class="currency-table">
                    <thead>
                        <tr>
                            <th>Currency Code</th>
                            <th>Currency Name</th>
                            <th>Exchange Rate (USD)</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody id="currency-table-body">
                        <?php foreach ($currencies as $code => $c): ?>
                            <tr data-code="<?= $e($code) ?>"
                                data-name="<?= $e($c['name']) ?>"
                                data-rate="<?= $e($c['rate']) ?>">
                                <td><?= $e($code) ?></td>
                                <td><?= $e($c['name']) ?></td>
                                <td><?= $e($c['rate']) ?></td>
                                <td><button type="button" class="view-button">View Details</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Shown when there is no data, or when a search matches nothing -->
            <p id="currency-message" class="currency-message" <?= $currencies ? 'hidden' : '' ?>>
                Currency information is not available yet.
            </p>
        </section>

        <!-- Details panel: hidden until "View Details" is clicked -->
        <section id="currency-details" class="currency-details" hidden>
            <h2>Currency Details</h2>

            <div class="detail-row">
                <span>Currency Code</span>
                <span id="detail-code"></span>
            </div>

            <div class="detail-row">
                <span>Currency Name</span>
                <span id="detail-name"></span>
            </div>

            <div class="detail-row">
                <span>Exchange Rate (USD)</span>
                <span id="detail-rate"></span>
            </div>

            <button type="button" id="close-details" class="close-details-button">
                Close Details
            </button>
        </section>

        <div class="currencies-footer">
            <a href="home.php" class="back-home-button">Back to Home</a>
        </div>

    </main>

    <script>
        const $ = id => document.getElementById(id);
        const rows = document.querySelectorAll('#currency-table-body tr');
        const message = $('currency-message');
        const details = $('currency-details');

        // Search: show only rows whose code or name contains the typed text.
        $('currency-search').addEventListener('input', function () {
            if (rows.length === 0) return;   // no data, so keep the "not available" message

            const text = this.value.toLowerCase().trim();
            let shown = 0;

            rows.forEach(row => {
                const match = row.dataset.code.toLowerCase().includes(text)
                           || row.dataset.name.toLowerCase().includes(text);
                row.hidden = !match;
                if (match) shown++;
            });

            // Only visible when nothing matched.
            message.textContent = 'No matching currencies found.';
            message.hidden = shown > 0;
        });

        // View Details: one listener on the table body handles every row's button.
        $('currency-table-body').addEventListener('click', function (evt) {
            if (!evt.target.classList.contains('view-button')) return;

            const row = evt.target.closest('tr');
            $('detail-code').textContent = row.dataset.code;
            $('detail-name').textContent = row.dataset.name;
            $('detail-rate').textContent = row.dataset.rate;

            details.hidden = false;
            details.scrollIntoView({ behavior: 'smooth', block: 'nearest' });   // keep it in view on long lists
        });

        // Close Details
        $('close-details').addEventListener('click', () => { details.hidden = true; });
    </script>

</body>
</html>