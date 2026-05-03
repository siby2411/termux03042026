<?php
require_once 'config_twilio.php';
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test WhatsApp Twilio - Dieynaba GP Holding</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2>📱 Test envoi WhatsApp avec Twilio</h2>
        </div>
        <div class="card-body">
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
                $message = $_POST['message'];
                $result = sendWhatsAppTwilio(TWILIO_TEST_TO, $message);
                if ($result['success']) {
                    echo '<div class="alert alert-success">✅ Message envoyé avec succès à ' . TWILIO_TEST_TO . '</div>';
                } else {
                    echo '<div class="alert alert-danger">❌ Erreur : HTTP ' . $result['http_code'] . '<br>' . print_r($result['response'], true) . '</div>';
                }
            }
            ?>
            <form method="post">
                <div class="mb-3">
                    <label>Message à envoyer à votre numéro personnel (+221 77 654 28 03) :</label>
                    <textarea name="message" class="form-control" rows="3">📦 Test depuis Dieynaba GP Holding – Votre colis est prêt à être expédié. Suivez-le via http://127.0.0.1:8000/suivi.php</textarea>
                </div>
                <button type="submit" class="btn btn-success">Envoyer via Twilio</button>
                <a href="dashboard.php" class="btn btn-secondary">Retour Dashboard</a>
            </form>
            <hr>
            <div class="alert alert-info">
                <strong>Configuration actuelle :</strong><br>
                From (WhatsApp Business) : <?= TWILIO_WHATSAPP_FROM ?><br>
                To (numéro de test) : <?= TWILIO_TEST_TO ?><br>
                Account SID : <?= substr(TWILIO_ACCOUNT_SID, 0, 10) ?>...
            </div>
            <p class="text-muted"><small>Assurez-vous que le numéro <strong><?= TWILIO_WHATSAPP_FROM ?></strong> soit bien un numéro WhatsApp Business activé sur Twilio (sandbox ou vérifié).</small></p>
        </div>
    </div>
</body>
</html>
