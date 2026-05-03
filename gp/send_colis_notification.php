<?php
require_once 'db_connect.php';
require_once 'config_twilio.php';

session_start();
if (!isset($_SESSION['admin_logged'])) {
    die("Accès réservé à l'administration");
}

// FORCER L'ENVOI VERS VOTRE NUMÉRO DE TEST
define('TEST_PHONE_NUMBER', '+221776542803');

$colis_list = $pdo->query("
    SELECT c.id, c.numero_suivi, c.statut, c.description, c.lieu_depart, c.lieu_arrivee, c.poids_kg,
           e.nom as expediteur_nom, e.telephone as expediteur_tel,
           d.nom as destinataire_nom, d.telephone as destinataire_tel
    FROM colis c
    LEFT JOIN clients e ON c.client_expediteur_id = e.id
    LEFT JOIN clients d ON c.client_destinataire_id = d.id
    ORDER BY c.id DESC
")->fetchAll();

$message_envoye = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notification'])) {
    $colis_id = (int)$_POST['colis_id'];
    $type = $_POST['type'];
    $statut = $_POST['statut'];
    $message_perso = trim($_POST['message_personnalise'] ?? '');
    
    $stmt = $pdo->prepare("
        SELECT c.*, e.nom as expediteur_nom, e.telephone as expediteur_tel,
               d.nom as destinataire_nom, d.telephone as destinataire_tel
        FROM colis c
        LEFT JOIN clients e ON c.client_expediteur_id = e.id
        LEFT JOIN clients d ON c.client_destinataire_id = d.id
        WHERE c.id = ?
    ");
    $stmt->execute([$colis_id]);
    $colis = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($colis) {
        $nom_client = ($type == 'expediteur') ? $colis['expediteur_nom'] : $colis['destinataire_nom'];
        $lien_suivi = "http://127.0.0.1:8000/suivi.php?numero=" . urlencode($colis['numero_suivi']);
        
        // Message formaté
        $message = "╔══════════════════════════════════════╗\n";
        $message .= "║     🌍 DIEYNABA GP HOLDING 🌍       ║\n";
        $message .= "╚══════════════════════════════════════╝\n\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "👋 Bonjour *" . strtoupper($nom_client) . "*,\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "📦 *MISE À JOUR DE VOTRE COLIS*\n";
        $message .= "────────────────────────────────────\n";
        $message .= "🔹 N° de suivi : `" . $colis['numero_suivi'] . "`\n";
        $message .= "🔹 Statut actuel : *" . strtoupper($statut) . "*\n";
        $message .= "🔹 Description : " . $colis['description'] . "\n";
        $message .= "🔹 Poids : " . $colis['poids_kg'] . " kg\n";
        $message .= "🔹 Itinéraire : " . $colis['lieu_depart'] . " → " . $colis['lieu_arrivee'] . "\n\n";
        
        if (!empty($message_perso)) {
            $message .= "💬 *Message personnalisé :*\n";
            $message .= "────────────────────────────────────\n";
            $message .= $message_perso . "\n\n";
        }
        
        $message .= "📍 *SUIVI EN TEMPS RÉEL*\n";
        $message .= "────────────────────────────────────\n";
        $message .= "🔗 " . $lien_suivi . "\n\n";
        
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "📞 Service client : +33 7 58 68 63 48\n";
        $message .= "💬 WhatsApp : +221 77 654 28 03\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "Merci de votre confiance ! 🙏\n";
        $message .= "_Dieynaba GP Holding - Le pont entre l'Afrique et l'Europe_";
        
        // ENVOI VERS VOTRE NUMÉRO DE TEST UNIQUEMENT
        $result = sendWhatsAppTwilio('whatsapp:' . TEST_PHONE_NUMBER, $message);
        
        if ($result['success']) {
            $message_envoye = "✅ Message envoyé avec succès à votre numéro " . TEST_PHONE_NUMBER;
        } else {
            $error_message = "❌ Erreur d'envoi : " . ($result['error'] ?? 'Inconnue');
        }
    } else {
        $error_message = "❌ Colis non trouvé";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Envoi notification colis - Dieynaba GP Holding</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1a1a2e, #16213e); min-height: 100vh; }
        .container { background: white; border-radius: 20px; padding: 30px; margin-top: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
        .preview-card { background: #f0f2f5; border-radius: 15px; padding: 20px; font-family: monospace; white-space: pre-wrap; font-size: 11px; max-height: 450px; overflow-y: auto; }
        .btn-send { background: #25D366; color: white; border: none; padding: 12px 25px; border-radius: 30px; font-weight: bold; }
        .btn-send:hover { background: #20b859; }
    </style>
</head>
<body>
<div class="container">
    <h2><i class="fab fa-whatsapp"></i> Envoi de notification WhatsApp - Colis</h2>
    <p class="text-muted">Testez l'envoi de notifications pour vos colis vers votre numéro <strong><?= TEST_PHONE_NUMBER ?></strong></p>
    
    <?php if ($message_envoye): ?>
        <div class="alert alert-success"><?= $message_envoye ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header bg-primary text-white">📤 Formulaire d'envoi</div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label>📦 Colis</label>
                            <select name="colis_id" class="form-select" required>
                                <option value="">Sélectionner un colis</option>
                                <?php foreach ($colis_list as $c): ?>
                                    <option value="<?= $c['id'] ?>">
                                        <?= htmlspecialchars($c['numero_suivi']) ?> - <?= $c['description'] ?> (<?= $c['statut'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>👤 Type de notification (pour info uniquement)</label>
                            <select name="type" class="form-select">
                                <option value="expediteur">📤 Notification expéditeur</option>
                                <option value="destinataire">📥 Notification destinataire</option>
                            </select>
                            <small class="text-muted">Le message sera envoyé à votre numéro de test</small>
                        </div>
                        <div class="mb-3">
                            <label>📊 Statut du colis</label>
                            <select name="statut" class="form-select" required>
                                <option value="enregistre">📝 Enregistré</option>
                                <option value="depart">✈️ Départ</option>
                                <option value="transit">🚚 En transit</option>
                                <option value="arrivee">🛬 Arrivée</option>
                                <option value="livre">✅ Livré</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>💬 Message personnalisé (optionnel)</label>
                            <textarea name="message_personnalise" class="form-control" rows="2" placeholder="Ajoutez un message spécifique..."></textarea>
                        </div>
                        <button type="submit" name="send_notification" class="btn-send w-100">
                            <i class="fab fa-whatsapp"></i> Envoyer la notification (à mon numéro)
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-7">
            <div class="card">
                <div class="card-header bg-success text-white">📱 Aperçu du message (reçu sur votre téléphone)</div>
                <div class="card-body preview-card">
                    ╔══════════════════════════════════════╗<br>
                    ║     🌍 DIEYNABA GP HOLDING 🌍       ║<br>
                    ╚══════════════════════════════════════╝<br><br>
                    👋 Bonjour *NOM_CLIENT*,<br><br>
                    📦 *MISE À JOUR DE VOTRE COLIS*<br>
                    🔹 N° de suivi : `NUMERO_SUIVI`<br>
                    🔹 Statut actuel : *STATUT*<br>
                    🔹 Description : DESCRIPTION DU COLIS<br>
                    🔹 Poids : XX kg<br>
                    🔹 Itinéraire : DEPART → ARRIVEE<br><br>
                    📍 *SUIVI EN TEMPS RÉEL*<br>
                    🔗 http://127.0.0.1:8000/suivi.php?numero=XXXXX<br><br>
                    📞 Service client : +33 7 58 68 63 48<br>
                    💬 WhatsApp : +221 77 654 28 03<br>
                    Merci de votre confiance ! 🙏<br>
                    Dieynaba GP Holding - Le pont entre l'Afrique et l'Europe
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-4 text-center">
        <a href="dashboard.php" class="btn btn-secondary">← Retour au Dashboard</a>
        <a href="test_all_notifications.php" class="btn btn-info">📱 Envoi groupé</a>
    </div>
</div>
</body>
</html>
