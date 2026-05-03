<?php
// Configuration CallMeBot - Dieynaba GP Holding
// Test avec deux numéros

// Numéro professionnel (clients) - CELUI QUI REÇOIT LES MESSAGES
define('CALLMEBOT_CLIENT_NUMBER', '221776542803'); // 77 654 28 03

// Numéro de test (vos tests internes)
define('CALLMEBOT_TEST_NUMBER', '221778084201');   // 77 808 42 01

// Votre API KEY (à récupérer en envoyant /start au +34 627 13 29 08)
define('CALLMEBOT_API_KEY', 'VOTRE_API_KEY_ICI');

function sendWhatsAppCallMeBot($message, $phone = null) {
    global $use_client_number;
    $phone = $phone ?? CALLMEBOT_TEST_NUMBER; // Par défaut, envoi au numéro de test
    $phone = ltrim($phone, '+');
    
    $url = "https://api.callmebot.com/whatsapp.php?phone=$phone&text=" . urlencode($message) . "&apikey=" . CALLMEBOT_API_KEY;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['success' => $http_code == 200, 'response' => $response];
}

// Envoi au numéro client (77 654 28 03)
function sendToClient($message) {
    return sendWhatsAppCallMeBot($message, CALLMEBOT_CLIENT_NUMBER);
}

// Envoi au numéro de test (77 808 42 01)
function sendToTest($message) {
    return sendWhatsAppCallMeBot($message, CALLMEBOT_TEST_NUMBER);
}

// Notification de colis (version test)
function notifyColisTest($numero_suivi, $statut) {
    $message = "🏢 DIEYNABA GP HOLDING (TEST)\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    $message .= "📦 Colis N°: $numero_suivi\n";
    $message .= "📊 Statut: $statut\n\n";
    $message .= "🔗 Suivi: http://127.0.0.1:8000/suivi.php?numero=$numero_suivi\n\n";
    $message .= "Test envoyé à " . CALLMEBOT_TEST_NUMBER;
    
    return sendToTest($message);
}
?>
