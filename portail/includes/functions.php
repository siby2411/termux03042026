<?php
function formatDate($date, $format = 'd/m/Y') {
    if (!$date) return '';
    $dt = new DateTime($date);
    return $dt->format($format);
}

function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    if (!$datetime) return '';
    $dt = new DateTime($datetime);
    return $dt->format($format);
}

function formatMoney($amount) {
    return number_format($amount, 0, ',', ' ') . ' FCFA';
}

function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function generateCode($prefix, $table, $column = 'code') {
    global $pdo;
    $stmt = $pdo->query("SELECT MAX($column) FROM $table WHERE $column LIKE '$prefix%'");
    $last = $stmt->fetchColumn();
    if ($last) {
        $num = (int)substr($last, strlen($prefix)) + 1;
    } else {
        $num = 1;
    }
    return $prefix . str_pad($num, 6, '0', STR_PAD_LEFT);
}

function generateOrderNumber() {
    return 'CMD-' . date('Ymd') . '-' . rand(1000, 9999);
}
?>
