
<?php
session_start();


 if (empty($_SESSION['user_id'])) {
     header('Location: login.php');
     exit;
 }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Currency Browser | Currency Trading</title>

    <link rel="stylesheet" href="assets/style.css?v=5">
    <link rel="stylesheet" href="assets/currencies.css?v=1">
</head>

<body class="home-page">

    <!-- Navigation Bar -->
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

    <!-- Currency Browser Content -->
    <main class="currencies-container">

        <div class="currencies-header">

            <h1>Currency Browser</h1>

            <p>
                Browse currencies, search by name or code,
                and view exchange rate information.
            </p>

        </div>

        <!-- Search Section -->
        <div class="currency-search-section">

            <label for="currency-search">
                Search Currencies
            </label>

            <input
                type="text"
                id="currency-search"
                placeholder="Search by currency name or code..."
                oninput="searchCurrencies()">

        </div>

        <!-- Currency Table -->
        <section class="currency-table-section">

            <h2>Available Currencies</h2>

            <div class="currency-table-wrapper">

                <table class="currency-table">

                    <thead>
                        <tr>
                            <th>Currency Code</th>
                            <th>Currency Name</th>
                            <th>Exchange Rate</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody id="currency-table-body">
                        <!-- Currency data will come from backend -->
                    </tbody>

                </table>

            </div>

            <p id="currency-message" class="currency-message">
                Currency information is not available yet.
            </p>

        </section>

        <!-- Currency Details -->
        <section
            id="currency-details"
            class="currency-details"
            hidden>

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
                <span>Exchange Rate</span>
                <span id="detail-rate"></span>
            </div>

            <button
                type="button"
                class="close-details-button"
                onclick="closeCurrencyDetails()">
                Close Details
            </button>

        </section>

        <!-- Back to Home -->
        <div class="currencies-footer">

            <a href="home.php" class="back-home-button">
                Back to Home
            </a>

        </div>

    </main>

    <script>
        let currencies = [];

        function setCurrencies(currencyData) {
            if (!Array.isArray(currencyData)) {
                return;
            }

            currencies = currencyData;
            renderCurrencies(currencies);
        }

        function renderCurrencies(currencyList) {
            const tableBody = document.getElementById(
                'currency-table-body'
            );

            const message = document.getElementById(
                'currency-message'
            );

            tableBody.innerHTML = '';

            if (currencyList.length === 0) {
                message.textContent =
                    currencies.length === 0
                    ? 'Currency information is not available yet.'
                    : 'No matching currencies found.';

                message.style.display = 'block';
                return;
            }

            message.style.display = 'none';

            currencyList.forEach(function(currency) {

                const row = document.createElement('tr');

                const codeCell = document.createElement('td');
                codeCell.textContent = currency.code ?? '';

                const nameCell = document.createElement('td');
                nameCell.textContent = currency.name ?? '';

                const rateCell = document.createElement('td');
                rateCell.textContent =
                    currency.rate ?? 'Not available';

                const actionCell = document.createElement('td');

                const viewButton = document.createElement('button');
                viewButton.type = 'button';
                viewButton.className = 'view-button';
                viewButton.textContent = 'View Details';

                viewButton.addEventListener('click', function() {
                    showCurrencyDetails(currency);
                });

                actionCell.appendChild(viewButton);

                row.appendChild(codeCell);
                row.appendChild(nameCell);
                row.appendChild(rateCell);
                row.appendChild(actionCell);

                tableBody.appendChild(row);
            });
        }

        function searchCurrencies() {
            const searchInput = document.getElementById(
                'currency-search'
            );

            const searchText = searchInput.value
                .toLowerCase()
                .trim();

            const filteredCurrencies = currencies.filter(
                function(currency) {

                    const code = String(currency.code ?? '')
                        .toLowerCase();

                    const name = String(currency.name ?? '')
                        .toLowerCase();

                    return code.includes(searchText) ||
                           name.includes(searchText);
                }
            );

            renderCurrencies(filteredCurrencies);
        }

        function showCurrencyDetails(currency) {
            document.getElementById('detail-code').textContent =
                currency.code ?? '';

            document.getElementById('detail-name').textContent =
                currency.name ?? '';

            document.getElementById('detail-rate').textContent =
                currency.rate ?? 'Not available';

            document.getElementById('currency-details').hidden = false;
        }

        function closeCurrencyDetails() {
            document.getElementById('currency-details').hidden = true;
        }

        renderCurrencies(currencies);
    </script>

</body>
</html>
