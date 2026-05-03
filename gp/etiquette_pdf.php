<?php
require_once 'db_connect.php';
require_once 'generer_qr.php';

if (!isset($_GET['id'])) {
    die("ID colis manquant");
}

$colis_id = (int)$_GET['id'];
$stmt = $pdo->prepare("
    SELECT c.*, e.nom as expediteur_nom, e.telephone as expediteur_tel, e.adresse as expediteur_adresse,
           d.nom as destinataire_nom, d.telephone as destinataire_tel, d.adresse as destinataire_adresse
    FROM colis c
    LEFT JOIN clients e ON c.client_expediteur_id = e.id
    LEFT JOIN clients d ON c.client_destinataire_id = d.id
    WHERE c.id = ?
");
$stmt->execute([$colis_id]);
$colis = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$colis) {
    die("Colis non trouvé");
}

$qr_file = generer_qr_colis($colis['numero_suivi']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Étiquette colis - <?= htmlspecialchars($colis['numero_suivi']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .etiquette { width: 210mm; margin: 0 auto; background: white; border: 1px solid #ddd; padding: 10mm; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #ff8c00; margin-bottom: 20px; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #ff8c00; font-size: 24px; }
        .header p { margin: 5px 0 0; color: #666; }
        .section { margin-bottom: 15px; }
        .section-title { font-weight: bold; background: #f0f0f0; padding: 8px; margin-bottom: 10px; border-left: 4px solid #ff8c00; }
        .qr-code { text-align: center; margin-top: 20px; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #999; border-top: 1px solid #ccc; padding-top: 10px; }
        .info-row { margin-bottom: 5px; }
        .info-label { font-weight: bold; display: inline-block; width: 120px; }
        .btn-print { display: block; width: 200px; margin: 20px auto; padding: 10px; background: #ff8c00; color: white; text-align: center; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn-print:hover { background: #e65c00; }
        @media print {
            body { background: white; padding: 0; }
            .etiquette { box-shadow: none; margin: 0; padding: 10mm; }
            .btn-print { display: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
<div class="etiquette">
    <div class="header">
        <h1>🏢 DIEYNABA GP HOLDING</h1>
        <p>Transport international • Suivi GPS • QR Code</p>
    </div>
    
    <div class="section">
        <div class="section-title">📦 INFORMATIONS COLIS</div>
        <div class="info-row"><span class="info-label">N° de suivi :</span> <strong><?= htmlspecialchars($colis['numero_suivi']) ?></strong></div>
        <div class="info-row"><span class="info-label">Description :</span> <?= htmlspecialchars($colis['description']) ?></div>
        <div class="info-row"><span class="info-label">Poids :</span> <?= $colis['poids_kg'] ?> kg</div>
        <div class="info-row"><span class="info-label">Statut :</span> <?= $colis['statut'] ?></div>
        <div class="info-row"><span class="info-label">Sens :</span> <?= $colis['sens'] == 'paris_dakar' ? '🇫🇷 Paris → Dakar 🇸🇳' : '🇸🇳 Dakar → Paris 🇫🇷' ?></div>
        <div class="info-row"><span class="info-label">Départ :</span> <?= $colis['lieu_depart'] ?? '-' ?></div>
        <div class="info-row"><span class="info-label">Arrivée :</span> <?= $colis['lieu_arrivee'] ?? '-' ?></div>
    </div>
    
    <div class="section">
        <div class="section-title">👤 EXPÉDITEUR</div>
        <div class="info-row"><span class="info-label">Nom :</span> <?= htmlspecialchars($colis['expediteur_nom']) ?></div>
        <div class="info-row"><span class="info-label">Téléphone :</span> <?= htmlspecialchars($colis['expediteur_tel']) ?></div>
        <div class="info-row"><span class="info-label">Adresse :</span> <?= nl2br(htmlspecialchars($colis['expediteur_adresse'] ?? '-')) ?></div>
    </div>
    
    <div class="section">
        <div class="section-title">🎯 DESTINATAIRE</div>
        <div class="info-row"><span class="info-label">Nom :</span> <?= htmlspecialchars($colis['destinataire_nom']) ?></div>
        <div class="info-row"><span class="info-label">Téléphone :</span> <?= htmlspecialchars($colis['destinataire_tel']) ?></div>
        <div class="info-row"><span class="info-label">Adresse :</span> <?= nl2br(htmlspecialchars($colis['destinataire_adresse'] ?? '-')) ?></div>
    </div>
    
    <?php if ($qr_file && file_exists(__DIR__ . '/' . $qr_file)): ?>
    <div class="qr-code">
        <img src="<?= $qr_file ?>" width="120" height="120">
        <p>🔍 Scannez ce QR code pour suivre votre colis en temps réel</p>
    </div>
    <?php else: ?>
    <div class="qr-code">
        <p style="color:#999;">QR code non disponible</p>
    </div>
    <?php endif; ?>
    
    <div class="footer">
        <p>📞 Paris: +33 7 58 68 63 48 | 📞 Dakar: +221 33 888 88 88</p>
        <p>📧 contact@dieynaba.com | www.dieynaba.com</p>
        <p>✈️ Vols hebdomadaires Paris ↔ Dakar (Mardis et Jeudis)</p>
    </div>
</div>
<div class="no-print" style="text-align:center; margin-top:20px;">
    <button class="btn-print" onclick="window.print()">🖨️ Imprimer l'étiquette</button>
    <button class="btn-print" style="background:#666;" onclick="window.close()">❌ Fermer</button>
</div>
</body>
</html>
<?php
// Si paramètre pdf présent, déclencher l'impression
if (isset($_GET['print'])) {
    echo "<script>window.onload = function() { window.print(); }</script>";
}
?>
