<?php
if (session_status() === PHP_SESSION_NONE) session_start();

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

function flash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function alertStock() {
    // Fonction d'alerte stock - peut être vide ou implémentée plus tard
    return '';
}

function getCategoryColor($categorie) {
    $colors = [
        'Charcuterie' => '#e74c3c',
        'Fromage' => '#f1c40f',
        'Volailles' => '#27ae60',
        'Poissons' => '#3498db',
        'Épicerie' => '#9b59b6'
    ];
    return $colors[$categorie] ?? '#7f8c8d';
}
?>
