<?php
// includes/functions.php
function sendAutomaticResponse($to, $type, $data) {
    $messages = [
        'inscription' => "Cher parent, votre demande d'inscription pour {$data['enfant']} a été reçue. Notre équipe vous contactera sous 48h. Merci de choisir OMEGA Transport.",
        'paiement' => "Paiement de {$data['montant']} FCFA confirmé pour {$data['enfant']}. Réf: {$data['reference']}",
        'absence' => "Information: Votre enfant {$data['enfant']} ne prendra pas le bus aujourd'hui. Merci de prévenir le chauffeur au {$data['chauffeur_tel']}",
        'rappel' => "Rappel: Paiement du transport pour {$data['enfant']} en attente. Veuillez régulariser avant le {$data['date_limite']}"
    ];
    
    $message = $messages[$type] ?? "OMEGA Transport: {$data['message']}";
    
    // Envoi SMS via API (à configurer avec Orange Sénégal ou Wave Business)
    // sendSMS($to, $message);
    
    // Log pour debug
    error_log("AUTO-RESPONSE to $to: $message");
    
    return $message;
}

function generatePassword($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($chars), 0, $length);
}

function getDistanceBetweenPoints($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthRadius * $c;
}
?>
