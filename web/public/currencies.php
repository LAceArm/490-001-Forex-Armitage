<?php
require_once __DIR__ . '/../broker/session_check.php';

// Currencies
$names = [
    'AED' => 'United Arab Emirates Dirham',
    'CAD' => 'Canadian Dollar',
    'CHF' => 'Swiss Franc',
    'CNY' => 'Chinese Yuan',
    'EUR' => 'Euro',
    'GBP' => 'British Pound',
    'JPY' => 'Japanese Yen',
    'USD' => 'US Dollar'
];

$currencies = [];

// Bother DB for rates
$reply = send_to_broker(['type' => 'get_rates']);

// Processing rates
foreach ($reply['rates'] ?? [] as $row) {
    $code = $row['quote_currency'];
    $rate = 1 / $row['rate'];

        $currencies[$code] = [
            'name' => $names[$code] ?? $code,
            'rate' => number_format($rate, $rate < 0.01 ? 6 : 4)
        ];
    }
}

ksort($currencies);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Currency Browser | Forex Armitage</title>
    <link rel="stylesheet" href="assets/style.css?v=5">
    <link rel="stylesheet" href="assets/currencies.css?v=1">
</head>

<body class="home-page">

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

<main class="currencies-container">

    <div class="currencies-header">
        <h1>Currency Browser</h1>
        <p>Browse currencies, search by name or code, and view exchange rate information.</p>
    </div>

    <div class="currency-search-section">
        <label for="currency-search">Search Currencies</label>
        <input type="text" id="currency-search"
               placeholder="Search by currency name or code..."
               onkeyup="searchCurrencies()">
    </div>

    <section class="currency-table-section">
        <h2>Available Currencies</h2>

        <div class="currency-table-wrapper">
            <table class="currency-table" id="currency-table">
                <thead>
                    <tr>
                        <th>Currency Code</th>
                        <th>Currency Name</th>
                        <th>Exchange Rate (USD)</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($currencies as $code => $currency): ?>
                         <tr>
                            <td><?= $code ?></td>
                            <td><?= currency['name'] ?></td>
                            <td><?= currency['rate'] ?></td>
                            <td>
                                <button class="view-button" onclick="showDetails(this)">View Details</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p id="currency-message" class="currency-message"
           <?= count($currencies) > 0 ? 'hidden' : '' ?>>
            Currency information is not available yet.
        </p>
    </section>

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

        <button class="close-details-button"
                onclick="closeDetails()">
            Close Details
        </button>
    </section>

    <div class="currencies-footer">
        <a href="home.php" class="back-home-button">Back to Home</a>
    </div>

</main>
<script>
function searchCurrencies() {
    let search = document.getElementById("currency-search").value.toLowerCase();
    let rows = document.querySelectorAll("#currency-table tbody tr");
    let message = document.getElementById("currency-message");
    let found = false;

    rows.forEach(function(row) {
        let text = row.innerText.toLowerCase();

        if (text.includes(search)) {
            row.hidden = false;
            found = true;
        } else {
            row.hidden = true;
        }
    });

    if (rows.length > 0) {
        message.innerText = "No matching currencies found.";
        message.hidden = found;
    }
}

function showDetails(button) {
    let row = button.parentElement.parentElement;
    let cells = row.getElementsByTagName("td");

    document.getElementById("detail-code").innerText = cells[0].innerText;
    document.getElementById("detail-name").innerText = cells[1].innerText;
    document.getElementById("detail-rate").innerText = cells[2].innerText;

    document.getElementById("currency-details").hidden = false;
    document.getElementById("currency-details").scrollIntoView();
}

function closeDetails() {
    document.getElementById("currency-details").hidden = true;
}
</script>

</body>
</html>
