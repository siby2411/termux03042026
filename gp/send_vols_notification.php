<?php
require_once 'auth.php';
require_once 'db_connect.php';
require_once 'config_twilio.php';

define('TEST_PHONE_NUMBER', 'whatsapp:+221776542803');

$vols = $pdo->query("SELECT * FROM vols WHERE date_depart >= NOW() ORDER BY date_depart ASC LIMIT 5")->fetchAll();

if (empty($vols)) {
    die("<div class='alert alert-warning'>Aucun vol à venir</div>");
}

$message = "╔══════════════════════════════════════╗\n";
$message .= "║     ✈️ HORAIRES DES VOLS ✈️        ║\n";
$message .= "║     DIEYNABA GP HOLDING            ║\n";
$message .= "╚══════════════════════════════════════╝\n\n";

foreach ($vols as $v) {
    $date_dep = new DateTime($v['date_depart']);
    $message .= "📅 " . $date_dep->format('d/m/Y') . " - " . $v['numero_vol'] . "\n";
    $message .= "   🛫 " . $v['depart_ville'] . " " . $date_dep->format('H:i') . "\n";
    $message .= "   🛬 " . $v['arrivee_ville'] . "\n\n";
}

$message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$message .= "📞 Réservations : +33 7 58 68 63 48\n";
$message .= "💬 WhatsApp : +221 77 654 28 03\n";
$message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$message .= "Dieynaba GP Holding - Le pont entre l'Afrique et l'Europe";

$result = sendWhatsAppTwilio(TEST_PHONE_NUMBER, $message);

if ($result['success']) {
    echo "<div class='alert alert-success'>✅ Horaires des vols envoyés sur votre WhatsApp</div>";
} else {
    echo "<div class='alert alert-danger'>❌ Erreur d'envoi</div>";
}

echo '<a href="horaires_vols.php" class="btn btn-primary">← Retour aux horaires</a>';
?>
