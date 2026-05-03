<?php
require_once 'db_connect.php';
require_once 'config_twilio.php';

define('TEST_PHONE_NUMBER', 'whatsapp:+221776542803');

// Récupérer le premier colis
$colis = $pdo->query("SELECT id, numero_suivi, statut FROM colis LIMIT 1")->fetch();

if ($colis) {
    $lien_suivi = "http://127.0.0.1:8000/suivi.php?numero=" . urlencode($colis['numero_suivi']);
    
    $message = "╔══════════════════════════════════════╗\n";
    $message .= "║     🌍 DIEYNABA GP HOLDING 🌍       ║\n";
    $message .= "╚══════════════════════════════════════╝\n\n";
    $message .= "📦 *TEST AUTOMATIQUE*\n";
    $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $message .= "🔹 Colis N°: {$colis['numero_suivi']}\n";
    $message .= "🔹 Statut: {$colis['statut']}\n\n";
    $message .= "🔗 Suivi: {$lien_suivi}\n\n";
    $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $message .= "📞 Service client: +33 7 58 68 63 48\n";
    $message .= "💬 WhatsApp: +221 77 654 28 03";
    
    $result = sendWhatsAppTwilio(TEST_PHONE_NUMBER, $message);
    
    if ($result['success']) {
        echo "✅ Notification envoyée sur votre numéro pour le colis {$colis['numero_suivi']}";
    } else {
        echo "❌ Erreur: " . ($result['error'] ?? 'Inconnue');
    }
} else {
    echo "❌ Aucun colis trouvé";
}
?>
