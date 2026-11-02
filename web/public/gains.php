<?php

require_once __DIR__ . '/../broker/session_check.php';

$key = $_SESSION['session_key'];

$money = fn($v) => number_format((float)$v, 2);
$usd = fn($v) => ($v < 0 ? '-$' : '$') . number_format(abs($v), 2);
$cls = fn($v) => $v >= 0 ? 'positive' : 'negative';

$portfolio = send_to_broker([
    'type' => 'get_portfolio',
    'session_key' => $key
]);

$loaded = !empty($portfolio['success']);
$positions = $loaded ? ($portfolio['positions'] ?? []) : [];

$cash = 0;
$holdingsValue = 0;

foreach ($positions as $p) {

 if ($p['currency'] === 'USD') {
    $cash += (float)$p['amount'];
 } else {
    $holdingsValue += (float)($p['usd_value'] ?? 0);
 }

}

$initialValue = $loaded ? (float)$portfolio['starting_cash'] : null;

$currentValue = $loaded ? $cash + $holdingsValue : null;

$totalGain = $loaded ? $currentValue - $initialValue : null;

$gainPercentage = ($loaded && $initialValue > 0)
    ? ($totalGain / $initialValue) * 100
    : null;

?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Portfolio Gains | Forex Armitage</title>

<link rel="stylesheet" href="assets/style.css">
<link rel="stylesheet" href="assets/gains.css">
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

<main class="gains-container">

<div class="gains-header">
<h1>Portfolio Gains</h1>
<p>Track the performance of your virtual currency holdings.</p>
</div>

<div class="gains-summary">

<div class="gains-card">
<h2>Initial Portfolio Value</h2>
<p>
<?= $initialValue !== null ? '$' . $money($initialValue) : 'Not available' ?>
</p>
</div>

<div class="gains-card">
<h2>Current Portfolio Value</h2>
<p>
<?= $currentValue !== null ? '$' . $money($currentValue) : 'Not available' ?>
</p>
</div>

<div class="gains-card">
<h2>Total Gain / Loss</h2>
<p class="<?= $totalGain !== null ? $cls($totalGain) : '' ?>">
<?= $totalGain !== null ? $usd($totalGain) : 'Not available' ?>
</p>
</div>

<div class="gains-card">
<h2>Performance</h2>
<p class="<?= $gainPercentage !== null ? $cls($gainPercentage) : '' ?>">
<?= $gainPercentage !== null ? number_format($gainPercentage, 2) . '%' : 'Not available' ?>
</p>
</div>

</div>

<section class="gains-holdings">

<h2>Current Holdings</h2>

<div class="gains-table-wrapper">

<table class="gains-table">

<thead>
<tr>
<th>Currency</th>
<th>Amount Owned</th>
<th>Current Value (USD)</th>
</tr>
</thead>

<tbody>

<?php foreach ($positions as $p): ?>

<tr>

<td>
<?= htmlspecialchars($p['currency']) ?>
</td>

<td>
<?= number_format((float)$p['amount'], 2) ?>
</td>

<td>

<?php if ($p['currency'] === 'USD'): ?>

$<?= $money($p['amount']) ?>

<?php else: ?>

$<?= $money($p['usd_value'] ?? 0) ?>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php if (!$loaded): ?>

<p class="gains-message">
Portfolio performance information is not available.
</p>

<?php endif; ?>

</section>

<div class="gains-actions">

<a href="portfolio.php" class="gains-button">
View Portfolio
</a>

<a href="home.php" class="gains-back-button">
Back to Home
</a>

</div>

</main>

</body>
</html>
