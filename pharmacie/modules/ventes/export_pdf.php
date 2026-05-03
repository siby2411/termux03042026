<?php
require_once '../../core/Auth.php';
require_once '../../core/Database.php';
require_once '../../config/config.php';
Auth::check();

$date = $_GET['date'] ?? date('Y-m-d');

// 1. Récupération des ventes groupées
$ventes = Database::query("
    SELECT v.*, u.nom as caissier 
    FROM ventes v 
    JOIN utilisateurs u ON v.utilisateur_id = u.id 
    WHERE DATE(v.date_vente) = ? AND v.statut='validee'
", [$date]);

// 2. Récupération du détail des articles pour le mouvement de stock
$mouvements = Database::query("
    SELECT m.denomination, SUM(vl.quantite) as total_vendu, m.stock_actuel
    FROM vente_lignes vl
    JOIN ventes v ON vl.vente_id = v.id
    JOIN medicaments m ON vl.medicament_id = m.id
    WHERE DATE(v.date_vente) = ? AND v.statut='validee'
    GROUP BY m.id
", [$date]);

// 3. Totaux
$totaux = Database::queryOne("
    SELECT SUM(montant_ttc) as ttc, SUM(montant_ttc/1.18) as ht, SUM(montant_ttc - (montant_ttc/1.18)) as tva 
    FROM ventes WHERE DATE(date_vente) = ? AND statut='validee'
", [$date]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport_Omega_<?= $date ?></title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 12px; color: #333; padding: 40px; }
        .header { border-bottom: 2px solid #00713e; padding-bottom: 10px; margin-bottom: 30px; }
        .logo { font-size: 20px; font-weight: bold; color: #01291a; }
        .logo span { color: #00713e; }
        .title { text-align: center; text-transform: uppercase; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .text-right { text-align: right; }
        .footer { margin-top: 50px; font-size: 10px; text-align: center; color: #777; }
        .summary-box { background: #f9f9f9; padding: 15px; border: 1px solid #eee; margin-bottom: 30px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #00713e; color: white; border: none; cursor: pointer;">Imprimer / Enregistrer en PDF</button>
        <a href="../../index.php" style="margin-left: 10px;">Retour au Dashboard</a>
    </div>

    <div class="header">
        <div class="logo">Ω OMEGA INFORMATIQUE <span>CONSULTING</span></div>
        <div>Gestion Pharmacie - Dakar, Sénégal</div>
    </div>

    <div class="title">
        <h2>Rapport de Ventes & Mouvements de Stock</h2>
        <p>Date : <strong><?= date('d/m/Y', strtotime($date)) ?></strong></p>
    </div>

    <div class="summary-box">
        <h3 style="margin-top:0;">Résumé Comptable (FCFA)</h3>
        <table style="border:none; margin-bottom:0;">
            <tr style="border:none;">
                <td style="border:none;">Total HT : <strong><?= number_format($totaux['ht'], 0, ',', ' ') ?></strong></td>
                <td style="border:none;">TVA Collectée (18%) : <strong><?= number_format($totaux['tva'], 0, ',', ' ') ?></strong></td>
                <td style="border:none; font-size:16px; color:#00713e;">TOTAL TTC : <strong><?= number_format($totaux['ttc'], 0, ',', ' ') ?> FCFA</strong></td>
            </tr>
        </table>
    </div>

    <h3>Détail des Transactions</h3>
    <table>
        <thead>
            <tr>
                <th>Référence</th>
                <th>Heure</th>
                <th>Mode</th>
                <th>Caissier</th>
                <th class="text-right">Montant TTC</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($ventes as $v): ?>
            <tr>
                <td><?= $v['reference'] ?></td>
                <td><?= date('H:i', strtotime($v['date_vente'])) ?></td>
                <td><?= ucfirst($v['mode_paiement']) ?></td>
                <td><?= $v['caissier'] ?></td>
                <td class="text-right fw-bold"><?= number_format($v['montant_ttc'], 0, ',', ' ') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Impact sur le Stock</h3>
    <table>
        <thead>
            <tr>
                <th>Médicament</th>
                <th class="text-right">Quantité Vendue</th>
                <th class="text-right">Stock Restant Actuel</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($mouvements as $m): ?>
            <tr>
                <td><?= $m['denomination'] ?></td>
                <td class="text-right"><?= $m['total_vendu'] ?></td>
                <td class="text-right" style="color: <?= $m['stock_actuel'] <= 10 ? 'red' : 'green' ?>; font-weight:bold;">
                    <?= $m['stock_actuel'] ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        Document généré par Omega Informatique CONSULTING - Système de Gestion Pharmaceutique Pro.
    </div>
</body>
</html>
