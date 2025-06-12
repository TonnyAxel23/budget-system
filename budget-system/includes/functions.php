<?php
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

function getCurrentMonthYear() {
    return [
        'month' => date('m'),
        'year' => date('Y'),
        'month_name' => date('F')
    ];
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
?>