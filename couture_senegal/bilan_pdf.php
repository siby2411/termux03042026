<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

$mois = $_GET['mois'] ?? date('m');
$annee = $_GET['annee'] ?? date('Y');
$nom_mois = ["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"][(int)$mois-1];

// 1. Chiffres Clés
$revenus = $pdo->query("SELECT SUM(montant) FROM paiements WHERE MONTH(date_paiement) = '$mois' AND YEAR(date_paiement) = '$annee'")->fetchColumn() ?: 0;
$depenses = $pdo->query("SELECT SUM(montant) FROM depenses WHERE MONTH(date_dep) = '$mois' AND YEAR(date_dep) = '$annee'")->fetchColumn() ?: 0;
$reliquats = $pdo->query("SELECT SUM(reste) FROM factures WHERE reste > 0")->fetchColumn() ?: 0;
$benefice = $revenus - $depenses;

// 2. Top 5 Dépenses
$top_depenses = $pdo->query("SELECT description, montant, date_dep FROM depenses WHERE MONTH(date_dep) = '$mois' AND YEAR(date_dep) = '$annee' ORDER BY montant DESC LIMIT 5")->fetchAll();

// 3. Commandes du mois
$nb_cmd = $pdo->query("SELECT COUNT(*) FROM commandes WHERE MONTH(date_commande) = '$mois' AND YEAR(date_commande) = '$annee'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bilan_<?= $nom_mois ?>_<?= $annee ?></title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .page { width: 210mm; min-height: 297mm; padding: 20mm; margin: auto; background: white; }
        .header { border-bottom: 3px solid #d4af37; padding-bottom: 10px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .logo-box { background: #000; color: #d4af37; padding: 10px 20px; font-weight: bold; border-radius: 4px; }
        .title { text-align: center; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 40px; }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
        .stat-card { border: 1px solid #eee; padding: 20px; text-align: center; border-radius: 8px; }
        .stat-card h3 { margin: 0; color: #d4af37; font-size: 22px; }
        .stat-card p { margin: 5px 0 0; color: #666; text-transform: uppercase; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f8f9fa; text-align: left; padding: 12px; border-bottom: 2px solid #eee; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .footer { margin-top: 50px; border-top: 1px solid #eee; padding-top: 20px; font-size: 10px; color: #999; text-align: center; }
        .signature { margin-top: 60px; display: flex; justify-content: flex-end; }
        .sig-box { border-top: 1px solid #000; width: 200px; text-align: center; padding-top: 10px; font-weight: bold; }
        
        @media print {
            .no-print { display: none; }
            body { background: none; }
            .page { margin: 0; border: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="background: #f1f5f9; padding: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #000; color: #d4af37; border: 1px solid #d4af37; cursor: pointer; font-weight: bold; border-radius: 5px;">
            🖨️ TELECHARGER / IMPRIMER LE BILAN PDF
        </button>
        <p style="font-size: 12px; color: #666; margin-top: 10px;">Note : Choisissez "Enregistrer au format PDF" dans l'imprimante.</p>
    </div>

    <div class="page">
        <div class="header">
            <div class="logo-box">OMEGA INFORMATIQUE CONSULTING</div>
            <div style="text-align: right;">
                <div style="font-weight: bold;">CoutureSn Pro v2.0</div>
                <div style="font-size: 12px;">Dakar, Sénégal</div>
            </div>
        </div>

        <div class="title">
            <h2 style="margin:0;">Note de Synthèse Mensuelle</h2>
            <div style="color: #d4af37; font-weight: bold;"><?= $nom_mois ?> <?= $annee ?></div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= number_format($revenus, 0, ',', ' ') ?> F</h3>
                <p>Revenus Encaissés</p>
            </div>
            <div class="stat-card">
                <h3><?= number_format($depenses, 0, ',', ' ') ?> F</h3>
                <p>Dépenses Totales</p>
            </div>
            <div class="stat-card" style="background: #fffdf5; border: 1px solid #d4af37;">
                <h3><?= number_format($benefice, 0, ',', ' ') ?> F</h3>
                <p>Bénéfice Net</p>
            </div>
        </div>

        <h4 style="border-left: 4px solid #000; padding-left: 10px;">Détails de l'Activité</h4>
        <table>
            <tr>
                <td>Nombre de nouvelles commandes</td>
                <td style="text-align: right; font-weight: bold;"><?= $nb_cmd ?></td>
            </tr>
            <tr>
                <td>Total des reliquats dehors (Dettes clients)</td>
                <td style="text-align: right; font-weight: bold; color: #dc3545;"><?= number_format($reliquats, 0, ',', ' ') ?> F</td>
            </tr>
        </table>

        <h4 style="border-left: 4px solid #d4af37; padding-left: 10px;">Top 5 des Dépenses</h4>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th style="text-align: right;">Montant</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($top_depenses as $td): ?>
                <tr>
                    <td><?= date('d/m', strtotime($td['date_dep'])) ?></td>
                    <td><?= htmlspecialchars($td['description']) ?></td>
                    <td style="text-align: right; font-weight: bold;"><?= number_format($td['montant'], 0, ',', ' ') ?> F</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="signature">
            <div class="sig-box">
                La Direction OMEGA<br>
                <small style="font-weight: normal;">Fait à Dakar, le <?= date('d/m/Y') ?></small>
            </div>
        </div>

        <div class="footer">
            Document généré par CoutureSn Pro - Propriété exclusive de OMEGA INFORMATIQUE CONSULTING
        </div>
    </div>

</body>
</html>
