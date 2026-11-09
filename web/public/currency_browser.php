<?php
require_once __DIR__ . '/../broker/session_check.php'; // Loads session check
require_once __DIR__ . '/../data/currencies.php'; // Defines $currencyNames (e.g., 'USD' => US Dollar)

$currencyRows = [];

$reply = send_to_broker(['type' => 'get_rates']); // Bother DB for rates

// Processing rates
foreach ($reply['rates'] as $row) {
    $currencyCode = $row['currency_code'];
    $rate = 1 / $row['units_per_usd'];

    $currencyRows[$currencyCode] = [
        'name' => $currencyNames[$currencyCode],
        'rate' => number_format($rate, 6)
    ];
}

ksort($currencyRows);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Currency Browser | Forex Armitage</title>
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

    <main class="container py-5">

        <div class="text-center mb-4">
            <h1 class="fw-bold fs-2 mb-3">Currency Browser</h1>
            <p class="text-secondary fs-6">
                Browse currencies, search by name or code, and view exchange rate information.
            </p>
        </div>

        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-4">

                <label for="currency-search" class="form-label fw-semibold mb-2">
                    Search Currencies
                </label>

                <input
                    type="text"
                    class="form-control"
                    id="currency-search"
                    placeholder="Search by currency name or code..."
                    onkeyup="searchCurrencies()"
                >

            </div>
        </div>

        <section class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-4">

                <h2 class="fs-5 fw-bold mb-3">Available Currencies</h2>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0" id="currency-table">

                        <thead class="table-light">
                            <tr>
                                <th>Currency Code</th>
                                <th>Currency Name</th>
                                <th>Exchange Rate (USD)</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($currencyRows as $currencyCode => $currency): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($currencyCode); ?></td>
                                    <td><?php echo htmlspecialchars($currency['name']); ?></td>
                                    <td><?php echo htmlspecialchars($currency['rate']); ?></td>
                                    <td>
                                        <button
                                            class="btn btn-primary btn-sm fw-semibold"
                                            onclick="showDetails(this)"
                                        >
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>

                <p
                    id="currency-message"
                    class="text-secondary text-center mt-3 mb-0"
                    <?php echo count($currencyRows) > 0 ? 'hidden' : ''; ?>
                >
                    Currency information is not available yet.
                </p>

            </div>
        </section>

        <section id="currency-details" class="card shadow-sm border-0 rounded-4 mb-4" hidden>
            <div class="card-body p-4">

                <h2 class="fs-5 fw-bold mb-4">Currency Details</h2>

                <div class="d-flex justify-content-between border-bottom py-3 gap-3">
                    <span class="fw-semibold">Currency Code</span>
                    <span id="detail-code"></span>
                </div>

                <div class="d-flex justify-content-between border-bottom py-3 gap-3">
                    <span class="fw-semibold">Currency Name</span>
                    <span id="detail-name"></span>
                </div>

                <div class="d-flex justify-content-between border-bottom py-3 gap-3">
                    <span class="fw-semibold">Exchange Rate (USD)</span>
                    <span id="detail-rate"></span>
                </div>

                <button
                    class="btn btn-outline-secondary fw-semibold mt-4"
                    onclick="closeDetails()"
                >
                    Close Details
                </button>

            </div>
        </section>

        <div class="text-center mt-4">
            <a href="home.php" class="btn btn-outline-primary fw-semibold px-4 py-2">
                Back to Home
            </a>
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
