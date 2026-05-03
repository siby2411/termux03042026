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

function calculerNuits($date_arrivee, $date_depart) {
    $arrivee = new DateTime($date_arrivee);
    $depart = new DateTime($date_depart);
    $interval = $arrivee->diff($depart);
    return $interval->days;
}

function calculerPrixTotal($chambre_id, $nuits) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT prix_nuit FROM chambres WHERE id = ?");
    $stmt->execute([$chambre_id]);
    $chambre = $stmt->fetch();
    return $chambre ? $chambre['prix_nuit'] * $nuits : 0;
}
?>
