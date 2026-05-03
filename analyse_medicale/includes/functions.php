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

function getUsernameById($id) {
    global $pdo;
    static $cache = [];
    if (isset($cache[$id])) return $cache[$id];
    $stmt = $pdo->prepare("SELECT CONCAT(first_name, ' ', last_name) as name FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $name = $stmt->fetchColumn();
    $cache[$id] = $name ?: "Utilisateur $id";
    return $cache[$id];
}
?>
