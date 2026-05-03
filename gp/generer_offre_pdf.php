<?php
require_once 'db_connect.php';

if (!isset($_GET['offre_id']) && !isset($_GET['client_id'])) {
    die("Paramètre offre_id ou client_id manquant");
}

// Récupération des données
if (isset($_GET['offre_id'])) {
    $stmt = $pdo->prepare("
        SELECT o.*, c.nom, c.telephone, c.email, c.adresse, c.code_client 
        FROM offres_services o
        JOIN clients c ON o.client_id = c.id
        WHERE o.id = ?
    ");
    $stmt->execute([$_GET['offre_id']]);
    $offre = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $client_id = (int)$_GET['client_id'];
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $offre = [
        'client_id' => $client_id,
        'nom' => $client['nom'],
        'telephone' => $client['telephone'],
        'email' => $client['email'],
        'adresse' => $client['adresse'],
        'code_client' => $client['code_client'],
        'type_offre' => 'fret',
        'montant_ht' => 45.00,
        'montant_tva' => 9.00,
        'montant_ttc' => 54.00,
        'description' => 'Expédition de colis vers le Sénégal ou la France, suivi GPS, QR code, notification WhatsApp.',
        'conditions' => 'Offre valable 30 jours. Paiement à réception.',
        'validite' => date('Y-m-d', strtotime('+30 days'))
    ];
}

if (!$offre) {
    die("Offre non trouvée");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Offre Dieynaba GP Holding - <?= htmlspecialchars($offre['nom']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .document { max-width: 210mm; margin: 0 auto; background: white; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460); color: white; padding: 20px; text-align: center; }
        .header img { max-height: 80px; margin-bottom: 10px; }
        .header h1 { font-size: 28px; margin: 5px 0; }
        .header p { font-size: 12px; opacity: 0.8; }
        .content { padding: 30px; }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 18px; font-weight: bold; color: #ff8c00; border-bottom: 2px solid #ff8c00; padding-bottom: 5px; margin-bottom: 15px; }
        .info-row { margin-bottom: 8px; }
        .info-label { font-weight: bold; display: inline-block; width: 140px; }
        .table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .table th, .table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .table th { background: #ff8c00; color: white; }
        .total { font-size: 20px; font-weight: bold; text-align: right; margin-top: 20px; padding-top: 10px; border-top: 2px solid #ff8c00; }
        .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 11px; color: #666; border-top: 1px solid #ddd; }
        .signature { margin-top: 40px; text-align: right; font-style: italic; }
        .btn-print { display: block; width: 200px; margin: 20px auto; padding: 10px; background: #ff8c00; color: white; text-align: center; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn-print:hover { background: #e65c00; }
        @media print {
            body { background: white; padding: 0; }
            .btn-print { display: none; }
            .document { box-shadow: none; margin: 0; }
            .header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .table th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
<div class="document">
    <div class="header">
        <img src="logo.jpg" alt="Dieynaba GP Holding" onerror="this.style.display='none'">
        <h1>🏢 DIEYNABA GP HOLDING</h1>
        <p>Transport international • Suivi GPS • Livraison express</p>
    </div>
    
    <div class="content">
        <div class="section">
            <div class="section-title">📄 OFFRE DE SERVICES</div>
            <div class="info-row"><span class="info-label">N° Offre :</span> <?= date('Ymd') ?>-<?= str_pad($offre['client_id'], 4, '0', STR_PAD_LEFT) ?></div>
            <div class="info-row"><span class="info-label">Date :</span> <?= date('d/m/Y') ?></div>
            <div class="info-row"><span class="info-label">Validité :</span> <?= date('d/m/Y', strtotime($offre['validite'])) ?></div>
        </div>
        
        <div class="section">
            <div class="section-title">🎯 DESTINATAIRE</div>
            <div class="info-row"><span class="info-label">Client :</span> <?= htmlspecialchars($offre['nom']) ?></div>
            <div class="info-row"><span class="info-label">Code client :</span> <?= htmlspecialchars($offre['code_client']) ?></div>
            <div class="info-row"><span class="info-label">Téléphone :</span> <?= htmlspecialchars($offre['telephone']) ?></div>
            <div class="info-row"><span class="info-label">Email :</span> <?= htmlspecialchars($offre['email']) ?></div>
            <div class="info-row"><span class="info-label">Adresse :</span> <?= nl2br(htmlspecialchars($offre['adresse'] ?? '-')) ?></div>
        </div>
        
        <div class="section">
            <div class="section-title">📦 DÉTAIL DE L'OFFRE</div>
            <p><strong>Type d'offre :</strong> <?= ucfirst($offre['type_offre']) ?></p>
            <p><?= nl2br(htmlspecialchars($offre['description'])) ?></p>
            
            <table class="table">
                <thead>
                    <tr><th>Service</th><th>Description</th><th>Inclus</th></tr>
                </thead>
                <tbody>
                    <tr><td>📦 Suivi GPS</td><td>Localisation en temps réel</td><td>✅</td></tr>
                    <tr><td>📱 QR Code</td><td>Suivi instantané par scan</td><td>✅</td></tr>
                    <tr><td>💬 WhatsApp</td><td>Notifications automatiques</td><td>✅</td></tr>
                    <tr><td>📄 PDF étiquette</td><td>Expédition professionnelle</td><td>✅</td></tr>
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">💰 TARIFS</div>
            <table class="table">
                <tr><td style="width:70%">Montant HT :</td><td><strong><?= number_format($offre['montant_ht'], 2) ?> €</strong></td></tr>
                <tr><td>TVA (20%) :</td><td><?= number_format($offre['montant_tva'], 2) ?> €</td></tr>
                <tr style="background:#f0f0f0;"><td><strong>TOTAL TTC :</strong></td><td><strong style="color:#ff8c00; font-size:18px;"><?= number_format($offre['montant_ttc'], 2) ?> €</strong></td></tr>
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">📋 CONDITIONS GÉNÉRALES</div>
            <p><?= nl2br(htmlspecialchars($offre['conditions'])) ?></p>
            <ul style="margin-top:10px; margin-left:20px;">
                <li>Paiement à réception de facture</li>
                <li>Délai de livraison indicatif: 48-72h</li>
                <li>Service client disponible 7j/7</li>
                <li>Assurance incluse pour les colis &lt; 500€</li>
            </ul>
        </div>
        
        <div class="signature">
            <p>Nous avons l'honneur, Monsieur/Madame, de vous soumettre notre offre.</p>
            <p>Dans l'attente de votre retour, nous vous prions d'agréer nos salutations distinguées.</p>
            <p style="margin-top:20px;"><strong>Dieynaba GP Holding</strong><br>
            Dakar, le <?= date('d/m/Y') ?></p>
        </div>
    </div>
    
    <div class="footer">
        <p>📞 Paris: +33 7 58 68 63 48 | 📞 Dakar: +221 33 888 88 88</p>
        <p>📧 contact@dieynaba.com | www.dieynaba.com</p>
        <p>✈️ Vols hebdomadaires Paris ↔ Dakar (Mardis et Jeudis)</p>
    </div>
</div>

<div style="text-align:center;">
    <button class="btn-print" onclick="window.print()">🖨️ Imprimer / PDF</button>
    <button class="btn-print" style="background:#666;" onclick="window.close()">❌ Fermer</button>
</div>

<script>
    // Déclencher l'impression automatique si demandé
    if (window.location.search.indexOf('print=1') > -1) {
        window.onload = function() { window.print(); }
    }
</script>
</body>
</html>
