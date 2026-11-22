<?php
// Displays numbers as money (1500 becomes $1,500.00)
function formatMoney($value) {
    if ($value === null) {
        return 'Not available';
    }
    return '$' . number_format($value, 2);
}

// Displays a rate, or 'Not available' if there isn't one
function formatRate($rate) {
    if ($rate === null) {
        return 'Not available';
    }

    return number_format($rate, $rate < 0.01 ? 6 : 4);
}

// Green text for a gain, red for a loss
function gainClass($value) {
    if ($value === null) {
        return '';
    }

    return $value >= 0 ? 'positive' : 'negative';
}
?>
