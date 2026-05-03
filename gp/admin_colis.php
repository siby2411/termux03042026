<?php
require_once 'auth.php';
require_once 'db_connect.php';
require_once 'config_twilio.php';
require_once 'qrcode_generator.php';

// FORCER L'ENVOI VERS VOTRE NUMÉRO DE TEST
define('TEST_PHONE_NUMBER', 'whatsapp:+221776542803');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['colis_id'], $_POST['nouveau_statut'])) {
    $colis_id = $_POST['colis_id'];
    $statut = $_POST['nouveau_statut'];
    $localisation = $_POST['localisation'] ?? '';
    
    // Récupérer les infos du colis avant mise à jour
    $stmt = $pdo->prepare("
        SELECT c.*, e.nom as expediteur_nom, d.nom as destinataire_nom
        FROM colis c
        LEFT JOIN clients e ON c.client_expediteur_id = e.id
        LEFT JOIN clients d ON c.client_destinataire_id = d.id
        WHERE c.id = ?
    ");
    $stmt->execute([$colis_id]);
    $colis = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($colis) {
        // Mettre à jour le statut
        $pdo->prepare("UPDATE colis SET statut = ? WHERE id = ?")->execute([$statut, $colis_id]);
        
        // Ajouter l'historique
        $pdo->prepare("INSERT INTO statuts_suivi (colis_id, statut, localisation) VALUES (?, ?, ?)")->execute([$colis_id, $statut, $localisation]);
        
        // Regénérer le QR code
        generateColisQRCode($colis_id, true);
        
        // ============================================================
        // ENVOI DE NOTIFICATION WHATSAPP AUTOMATIQUE
        // ============================================================
        $lien_suivi = "http://127.0.0.1:8000/suivi.php?numero=" . urlencode($colis['numero_suivi']);
        
        // Emojis par statut
        $emoji = [
            'enregistre' => '📝',
            'depart' => '✈️',
            'transit' => '🚚',
            'arrivee' => '🛬',
            'livre' => '✅'
        ];
        
        $icone = $emoji[$statut] ?? '📦';
        
        // Construction du message
        $message = "╔══════════════════════════════════════╗\n";
        $message .= "║     🌍 DIEYNABA GP HOLDING 🌍       ║\n";
        $message .= "╚══════════════════════════════════════╝\n\n";
        $message .= "{$icone} *MISE À JOUR COLIS N°{$colis['numero_suivi']}*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "📊 Nouveau statut : *" . strtoupper($statut) . "*\n";
        $message .= "📍 Localisation : " . ($localisation ?: ($statut == 'depart' ? 'Aéroport' : 'En cours')) . "\n\n";
        
        if ($statut == 'depart') {
            $message .= "✈️ Votre colis a pris son envol !\n";
        } elseif ($statut == 'arrivee') {
            $message .= "🛬 Votre colis est arrivé en France. Il sera traité sous 24h.\n";
        } elseif ($statut == 'livre') {
            $message .= "✅ Votre colis a été livré avec succès !\n";
        }
        $message .= "\n";
        
        $message .= "🔗 *SUIVI EN TEMPS RÉEL*\n";
        $message .= "────────────────────────────────────\n";
        $message .= "{$lien_suivi}\n\n";
        
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "📞 Service client : +33 7 58 68 63 48\n";
        $message .= "💬 WhatsApp : +221 77 654 28 03\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "Merci de votre confiance ! 🙏\n";
        $message .= "_Dieynaba GP Holding - Le pont entre l'Afrique et l'Europe_";
        
        // Envoi de la notification
        $result = sendWhatsAppTwilio(TEST_PHONE_NUMBER, $message);
        
        if ($result['success']) {
            echo "<div class='alert alert-success'>✅ Statut mis à jour et notification WhatsApp envoyée à votre numéro de test.</div>";
        } else {
            echo "<div class='alert alert-warning'>⚠️ Statut mis à jour mais notification WhatsApp non envoyée (erreur API).</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>❌ Colis non trouvé</div>";
    }
}

$cols = $pdo->query("
    SELECT c.id, c.numero_suivi, c.statut, e.nom as expediteur_nom 
    FROM colis c 
    LEFT JOIN clients e ON c.client_expediteur_id = e.id 
    ORDER BY c.derniere_mise_a_jour DESC
")->fetchAll();

include('header.php');
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<h2><i class="fas fa-boxes"></i> Administration des colis</h2>

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> 
    <strong>Notifications WhatsApp automatiques :</strong> Chaque changement de statut envoie une notification sur votre numéro de test <strong>+221 77 654 28 03</strong>.
</div>

<form method="post" class="card p-3 mb-4">
    <div class="row g-2">
        <div class="col-md-4">
            <select name="colis_id" class="form-select" required>
                <option value="">Sélectionner un colis</option>
                <?php foreach ($cols as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['numero_suivi']) ?> - <?= $c['expediteur_nom'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="nouveau_statut" class="form-select" required>
                <option value="enregistre">📝 Enregistré</option>
                <option value="depart">✈️ Départ</option>
                <option value="transit">🚚 En transit</option>
                <option value="arrivee">🛬 Arrivée</option>
                <option value="livre">✅ Livré</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="text" name="localisation" class="form-control" placeholder="Localisation (ex: Aéroport Dakar)">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-warning w-100"><i class="fas fa-save"></i> Mettre à jour</button>
        </div>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr><th>N° suivi</th><th>Expéditeur</th><th>Statut</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($cols as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['numero_suivi']) ?></div>
                <td><?= htmlspecialchars($c['expediteur_nom']) ?> </div>
                <td><span class="badge bg-secondary"><?= $c['statut'] ?></span></div>
                <td>
                    <a href="suivi_carte.php?numero=<?= urlencode($c['numero_suivi']) ?>" class="btn btn-sm btn-primary">🗺️ Carte</a>
                    <a href="etiquette_pdf.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger">📄 PDF</a>
                 </div>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('footer.php'); ?>
