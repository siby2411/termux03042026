<?php
require_once 'config_twilio.php';

// Tester l'envoi direct
$result = testWhatsApp();

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Test WhatsApp - Dieynaba GP Holding</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='container mt-5'>
    <div class='card'>
        <div class='card-header bg-primary text-white'>
            <h2><i class='fab fa-whatsapp'></i> Test de connexion WhatsApp</h2>
        </div>
        <div class='card-body'>
            <p><strong>Numéro de test :</strong> +221 77 654 28 03</p>
            <p><strong>Statut :</strong> ";
            
if ($result['success']) {
    echo "<span class='badge bg-success'>✅ Message envoyé avec succès</span>";
    echo "<p>Vérifiez votre téléphone, vous devriez avoir reçu le message.</p>";
} else {
    echo "<span class='badge bg-danger'>❌ Échec de l'envoi</span>";
    echo "<pre>Erreur : " . print_r($result, true) . "</pre>";
}

echo "
            </p>
            <hr>
            <a href='send_colis_notification.php' class='btn btn-success'>
                <i class='fab fa-whatsapp'></i> Envoyer une notification de colis
            </a>
            <a href='dashboard.php' class='btn btn-secondary'>Retour Dashboard</a>
        </div>
    </div>
</body>
</html>";
?>
