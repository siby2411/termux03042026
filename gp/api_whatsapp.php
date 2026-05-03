<?php
require_once 'db_connect.php';
require_once 'config_whatsapp.php';
require_once 'qrcode_generator.php';

// Action d'envoi du QR code par WhatsApp
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_qr') {
    header('Content-Type: application/json');
    
    $colis_id = (int)$_POST['colis_id'];
    $phone = $_POST['phone'] ?? DEFAULT_DESTINATAIRE_NUMBER;
    $type = $_POST['type'] ?? 'destinataire';
    
    // Si le numéro n'est pas fourni, utiliser le numéro de test
    if (empty($phone) || $phone == '') {
        $phone = DEFAULT_DESTINATAIRE_NUMBER;
    }
    
    $stmt = $pdo->prepare("
        SELECT c.*, e.nom as expediteur_nom, d.nom as destinataire_nom
        FROM colis c
        LEFT JOIN clients e ON c.client_expediteur_id = e.id
        LEFT JOIN clients d ON c.client_destinataire_id = d.id
        WHERE c.id = ?
    ");
    $stmt->execute([$colis_id]);
    $colis = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$colis) {
        echo json_encode(['success' => false, 'error' => 'Colis non trouvé']);
        exit;
    }
    
    $qr_result = generateColisQRCode($colis_id);
    $qr_url = "http://127.0.0.1:8000/" . $qr_result['filepath'];
    
    $nom_client = ($type == 'expediteur') ? $colis['expediteur_nom'] : $colis['destinataire_nom'];
    
    $result = sendQRCodeMessage(
        $phone, 
        $qr_url, 
        $colis['numero_suivi'], 
        $nom_client, 
        $type,
        $colis['statut'],
        $colis['lieu_depart'] ?? '',
        $colis['lieu_arrivee'] ?? ''
    );
    
    echo json_encode($result);
    exit;
}

// Action de notification de changement de statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'notify_status') {
    header('Content-Type: application/json');
    
    $colis_id = (int)$_POST['colis_id'];
    $nouveau_statut = $_POST['statut'];
    
    $stmt = $pdo->prepare("
        SELECT c.*, d.nom as destinataire_nom, d.telephone as destinataire_tel,
               e.nom as expediteur_nom, e.telephone as expediteur_tel
        FROM colis c
        LEFT JOIN clients d ON c.client_destinataire_id = d.id
        LEFT JOIN clients e ON c.client_expediteur_id = e.id
        WHERE c.id = ?
    ");
    $stmt->execute([$colis_id]);
    $colis = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$colis) {
        echo json_encode(['success' => false, 'error' => 'Colis non trouvé']);
        exit;
    }
    
    // Notifier le destinataire
    if ($colis['destinataire_tel']) {
        sendStatusNotification($colis['destinataire_tel'], $colis['numero_suivi'], $nouveau_statut, $colis['destinataire_nom'], 'destinataire');
    }
    
    // Notifier l'expéditeur (optionnel)
    if ($colis['expediteur_tel']) {
        sendStatusNotification($colis['expediteur_tel'], $colis['numero_suivi'], $nouveau_statut, $colis['expediteur_nom'], 'expediteur');
    }
    
    echo json_encode(['success' => true, 'message' => 'Notifications envoyées']);
    exit;
}

// Page de test (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['test'])) {
    header('Content-Type: text/html');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Test WhatsApp API - Dieynaba GP Holding</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="container mt-5">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h2><i class="fab fa-whatsapp"></i> Test WhatsApp API</h2>
            </div>
            <div class="card-body">
                <p><strong>Numéro de test :</strong> <?= TEST_RECIPIENT_NUMBER ?></p>
                <p><strong>Statut API :</strong> <span class="badge bg-success">✅ Connectée</span></p>
                
                <form method="post" action="api_whatsapp.php">
                    <input type="hidden" name="action" value="send_qr">
                    <div class="mb-3">
                        <label>ID du colis :</label>
                        <input type="number" name="colis_id" class="form-control" value="1" required>
                    </div>
                    <div class="mb-3">
                        <label>Numéro de téléphone :</label>
                        <input type="text" name="phone" class="form-control" value="221776542803">
                        <small class="text-muted">Format: 221776542803 (sans le +)</small>
                    </div>
                    <div class="mb-3">
                        <label>Type :</label>
                        <select name="type" class="form-select">
                            <option value="destinataire">Destinataire</option>
                            <option value="expediteur">Expéditeur</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Envoyer QR code test</button>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h3>📋 Derniers colis</h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <?php
                    $cols = $pdo->query("SELECT id, numero_suivi, statut FROM colis ORDER BY id DESC LIMIT 10")->fetchAll();
                    foreach ($cols as $c) {
                        echo "<tr><td>{$c['numero_suivi']}</td><td>{$c['statut']}</td>";
                        echo "<td><a href='api_whatsapp.php?colis_id={$c['id']}&test_notify=1' class='btn btn-sm btn-primary'>Tester notification</a></td></tr>";
                    }
                    ?>
                </table>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Test direct de notification
if (isset($_GET['test_notify']) && isset($_GET['colis_id'])) {
    $colis_id = (int)$_GET['colis_id'];
    $stmt = $pdo->prepare("SELECT statut FROM colis WHERE id = ?");
    $stmt->execute([$colis_id]);
    $colis = $stmt->fetch();
    if ($colis) {
        sendStatusNotification(DEFAULT_DESTINATAIRE_NUMBER, "TEST-{$colis_id}", $colis['statut'], "Test Client", 'destinataire');
        echo "Notification envoyée au numéro " . DEFAULT_DESTINATAIRE_NUMBER;
    } else {
        echo "Colis non trouvé";
    }
    exit;
}
?>
