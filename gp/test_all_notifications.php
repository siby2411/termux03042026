<?php
require_once 'db_connect.php';
require_once 'config_twilio.php';

session_start();
if (!isset($_SESSION['admin_logged'])) {
    die("Accès réservé à l'administration");
}

// Récupérer les colis
$colis_list = $pdo->query("
    SELECT c.id, c.numero_suivi, c.statut, c.description, c.lieu_depart, c.lieu_arrivee,
           e.nom as expediteur_nom, d.nom as destinataire_nom
    FROM colis c
    LEFT JOIN clients e ON c.client_expediteur_id = e.id
    LEFT JOIN clients d ON c.client_destinataire_id = d.id
    LIMIT 5
")->fetchAll();

$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($colis_list as $colis) {
        $lien_suivi = "http://127.0.0.1:8000/suivi.php?numero=" . urlencode($colis['numero_suivi']);
        
        $message = "🏢 DIEYNABA GP HOLDING\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "📦 COLIS N°: {$colis['numero_suivi']}\n";
        $message .= "📊 Statut: {$colis['statut']}\n";
        $message .= "📍 {$colis['lieu_depart']} → {$colis['lieu_arrivee']}\n";
        $message .= "📝 {$colis['description']}\n\n";
        $message .= "🔗 Suivi: {$lien_suivi}\n\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "Service client: +33 7 58 68 63 48\n";
        $message .= "WhatsApp: +221 77 654 28 03";
        
        $result = sendWhatsAppTwilio('whatsapp:+221776542803', $message);
        $results[] = [
            'colis' => $colis['numero_suivi'],
            'success' => $result['success']
        ];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test notifications - Dieynaba GP Holding</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2>📱 Envoi groupé de notifications</h2>
        </div>
        <div class="card-body">
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <div class="alert alert-info">
                    <strong>Résultats des envois :</strong>
                    <ul>
                    <?php foreach ($results as $r): ?>
                        <li>Colis <?= $r['colis'] ?> : <?= $r['success'] ? '✅ Envoyé' : '❌ Échec' ?></li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <p>Cette page enverra des notifications de test pour les 5 premiers colis vers votre numéro.</p>
            <form method="post">
                <button type="submit" class="btn btn-success">
                    <i class="fab fa-whatsapp"></i> Tester tous les colis
                </button>
                <a href="send_colis_notification.php" class="btn btn-primary">Interface détaillée</a>
                <a href="dashboard.php" class="btn btn-secondary">Retour Dashboard</a>
            </form>
        </div>
    </div>
</body>
</html>
