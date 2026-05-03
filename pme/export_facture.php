<?php
include 'includes/db.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM commandes WHERE id = ?");
$stmt->execute([$id]);
$cmd = $stmt->fetch();

if (!$cmd) { die("Commande introuvable."); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture_<?= $cmd['id'] ?></title>
    <style>
        body { font-family: sans-serif; padding: 40px; }
        .invoice-header { display: flex; justify-content: space-between; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .details { margin-top: 30px; width: 100%; border-collapse: collapse; }
        .details th, .details td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .total { font-weight: bold; font-size: 1.2em; text-align: right; margin-top: 20px; }
        @media print { .btn-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="btn-print" style="margin-bottom: 20px;">
        <button onclick="window.print()">Imprimer / Sauvegarder en PDF</button>
        <a href="comptabilite.php">Retour</a>
    </div>

    <div class="invoice-header">
        <div>
            <h1>FACTURE PME</h1>
            <p>123 Rue de l'Entreprise, Dakar</p>
        </div>
        <div style="text-align: right;">
            <p><strong>N° Facture :</strong> <?= $cmd['id'] ?></p>
            <p><strong>Date :</strong> <?= date('d/m/Y') ?></p>
        </div>
    </div>

    <p><strong>Client :</strong> <?= htmlspecialchars($cmd['client_nom']) ?></p>

    <table class="details">
        <thead>
            <tr style="background: #f4f4f4;">
                <th>Description</th>
                <th>Montant HT</th>
                <th>TVA (20%)</th>
                <th>Total TTC</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Prestation / Commande de marchandises</td>
                <td><?= number_format($cmd['total_ht'], 2) ?> €</td>
                <td><?= number_format($cmd['total_ht'] * 0.2, 2) ?> €</td>
                <td><?= number_format($cmd['total_ht'] * 1.2, 2) ?> €</td>
            </tr>
        </tbody>
    </table>

    <div class="total">
        TOTAL À PAYER : <?= number_format($cmd['total_ht'] * 1.2, 2) ?> €
    </div>
</body>
</html>
