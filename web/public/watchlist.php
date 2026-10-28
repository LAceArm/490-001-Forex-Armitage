
<?php
session_start();

$currencies = [
    'USD' => ['name' => 'US Dollar', 'rate' => null, 'change' => null],
    'EUR' => ['name' => 'Euro', 'rate' => null, 'change' => null],
    'GBP' => ['name' => 'British Pound', 'rate' => null, 'change' => null],
    'JPY' => ['name' => 'Japanese Yen', 'rate' => null, 'change' => null],
    'CNY' => ['name' => 'Chinese Yuan', 'rate' => null, 'change' => null],
    'CHF' => ['name' => 'Swiss Franc', 'rate' => null, 'change' => null],
    'AED' => ['name' => 'UAE Dirham', 'rate' => null, 'change' => null]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Watchlists | Forex Armitage</title>

    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/watchlist.css">
</head>

<body class="home-page">

    <nav class="main-navbar">
        <div class="main-nav-container">

            <a href="home.php" class="main-logo">
                Forex Armitage
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
            <h1>My Watchlists</h1>
            <p>Create watchlists to organize and track currencies.</p>
        </div>

        <section class="watchlist-card">

            <h2>Create New Watchlist</h2>

            <form id="create-watchlist-form" class="watchlist-form">

                <input
                    type="text"
                    id="watchlist-name"
                    placeholder="Enter watchlist name"
                    maxlength="50"
                    required
                >

                <button type="submit" class="watchlist-button">
                    Create Watchlist
                </button>

            </form>

        </section>

        <div id="watchlists-container"></div>

        <div class="watchlist-actions">

            <a href="currencies.php" class="watchlist-button">
                Browse Currencies
            </a>

            <a href="home.php" class="back-home-button">
                Back to Home
            </a>

        </div>

    </main>

    <script>
        const currencies = <?php
            echo json_encode(
                $currencies,
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            );
        ?>;

        let watchlists = [];
        let nextWatchlistId = 1;

        const watchlistsContainer = document.getElementById(
            'watchlists-container'
        );

        const createForm = document.getElementById(
            'create-watchlist-form'
        );

        createForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const nameInput = document.getElementById('watchlist-name');
            const name = nameInput.value.trim();

            if (name === '') {
                return;
            }

            const watchlist = {
                id: nextWatchlistId++,
                name: name,
                currencies: []
            };

            watchlists.push(watchlist);

            nameInput.value = '';

            renderWatchlists();
        });

        function addCurrency(watchlistId, currencyCode) {
            const watchlist = watchlists.find(function(list) {
                return list.id === watchlistId;
            });

            if (!watchlist || !Object.hasOwn(currencies, currencyCode)) {
                return;
            }

            if (watchlist.currencies.includes(currencyCode)) {
                return;
            }

            watchlist.currencies.push(currencyCode);

            renderWatchlists();
        }

        function removeCurrency(watchlistId, currencyCode) {
            const watchlist = watchlists.find(function(list) {
                return list.id === watchlistId;
            });

            if (!watchlist) {
                return;
            }

            watchlist.currencies = watchlist.currencies.filter(
                function(code) {
                    return code !== currencyCode;
                }
            );

            renderWatchlists();
        }

        function deleteWatchlist(watchlistId) {
            watchlists = watchlists.filter(function(list) {
                return list.id !== watchlistId;
            });

            renderWatchlists();
        }

        function formatRate(value) {
            if (value === null || value === undefined) {
                return 'Not available';
            }

            const number = Number(value);

            if (!Number.isFinite(number)) {
                return 'Not available';
            }

            return number.toFixed(4);
        }

        function formatChange(value) {
            if (value === null || value === undefined) {
                return 'Not available';
            }

            const number = Number(value);

            if (!Number.isFinite(number)) {
                return 'Not available';
            }

            return (number > 0 ? '+' : '') + number.toFixed(2) + '%';
        }

        function renderWatchlists() {
            watchlistsContainer.replaceChildren();

            if (watchlists.length === 0) {
                const emptyMessage = document.createElement('p');

                emptyMessage.className = 'watchlist-message';
                emptyMessage.textContent =
                    'No watchlists created yet. Create one to get started.';

                watchlistsContainer.appendChild(emptyMessage);
                return;
            }

            watchlists.forEach(function(watchlist) {
                const section = document.createElement('section');
                section.className = 'watchlist-card';

                const header = document.createElement('div');
                header.className = 'watchlist-section-header';

                const title = document.createElement('h2');
                title.textContent = watchlist.name;

                const deleteButton = document.createElement('button');
                deleteButton.type = 'button';
                deleteButton.className = 'delete-watchlist-button';
                deleteButton.textContent = 'Delete Watchlist';

                deleteButton.addEventListener('click', function() {
                    deleteWatchlist(watchlist.id);
                });

                header.append(title, deleteButton);

                const form = document.createElement('form');
                form.className = 'watchlist-form';

                const select = document.createElement('select');
                select.required = true;

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Select a currency';

                select.appendChild(defaultOption);

                Object.entries(currencies).forEach(function(entry) {
                    const code = entry[0];
                    const currency = entry[1];

                    const option = document.createElement('option');
                    option.value = code;
                    option.textContent = code + ' - ' + currency.name;

                    select.appendChild(option);
                });

                const addButton = document.createElement('button');
                addButton.type = 'submit';
                addButton.className = 'watchlist-button';
                addButton.textContent = 'Add Currency';

                form.append(select, addButton);

                form.addEventListener('submit', function(event) {
                    event.preventDefault();

                    addCurrency(watchlist.id, select.value);
                });

                const tableWrapper = document.createElement('div');
                tableWrapper.className = 'watchlist-table-wrapper';

                const table = document.createElement('table');
                table.className = 'watchlist-table';

                const thead = document.createElement('thead');
                const headerRow = document.createElement('tr');

                const headings = [
                    'Currency Code',
                    'Currency Name',
                    'Exchange Rate (USD)',
                    'Rate Change',
                    'Action'
                ];

                headings.forEach(function(heading) {
                    const th = document.createElement('th');
                    th.textContent = heading;

                    headerRow.appendChild(th);
                });

                thead.appendChild(headerRow);

                const tbody = document.createElement('tbody');

                watchlist.currencies.forEach(function(code) {
                    const currency = currencies[code];

                    if (!currency) {
                        return;
                    }

                    const row = document.createElement('tr');

                    const codeCell = document.createElement('td');
                    codeCell.textContent = code;

                    const nameCell = document.createElement('td');
                    nameCell.textContent = currency.name;

                    const rateCell = document.createElement('td');
                    rateCell.textContent = formatRate(currency.rate);

                    const changeCell = document.createElement('td');
                    changeCell.textContent = formatChange(currency.change);

                    const actionCell = document.createElement('td');

                    const removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.className = 'remove-button';
                    removeButton.textContent = 'Remove';

                    removeButton.addEventListener('click', function() {
                        removeCurrency(watchlist.id, code);
                    });

                    actionCell.appendChild(removeButton);

                    row.append(
                        codeCell,
                        nameCell,
                        rateCell,
                        changeCell,
                        actionCell
                    );

                    tbody.appendChild(row);
                });

                table.append(thead, tbody);
                tableWrapper.appendChild(table);

                section.append(header, form, tableWrapper);

                if (watchlist.currencies.length === 0) {
                    const emptyMessage = document.createElement('p');
                    emptyMessage.className = 'watchlist-message';
                    emptyMessage.textContent =
                        'This watchlist is empty. Add a currency to get started.';

                    section.appendChild(emptyMessage);
                }

                watchlistsContainer.appendChild(section);
            });
        }

        renderWatchlists();
    </script>

</body>
</html>
