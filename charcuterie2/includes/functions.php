<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * Formate une date au format français
 */
function formatDate($date, $format = 'd/m/Y') {
    if (!$date) return '';
    $dt = new DateTime($date);
    return $dt->format($format);
}

/**
 * Formate une date et heure
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    if (!$datetime) return '';
    $dt = new DateTime($datetime);
    return $dt->format($format);
}

/**
 * Formate un montant en FCFA
 */
function formatMoney($amount) {
    return number_format($amount, 0, ',', ' ') . ' FCFA';
}

/**
 * Échappe les caractères HTML
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Affiche un message flash
 */
function flash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

/**
 * Récupère et efface le message flash
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Redirection sécurisée
 */
function secureRedirect($url) {
    if (!headers_sent()) {
        header("Location: $url");
        exit;
    } else {
        echo "<script>window.location='$url';</script>";
        exit;
    }
}

/**
 * Génère une chaîne aléatoire
 */
function generateRandomString($length = 10) {
    return substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
}

/**
 * Formate un nombre
 */
function formatNumber($number, $decimals = 0) {
    return number_format($number, $decimals, ',', ' ');
}
?>
