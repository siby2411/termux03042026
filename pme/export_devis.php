<?php
include 'includes/db.php';
$id = (int)$_GET['id'];

$query = $pdo->prepare("
    SELECT d.*, c.nom as client_nom, c.email as client_email, p.designation, p.prix_unitaire 
    FROM devis d 
    JOIN clients c ON d.client_id = c.id 
    JOIN produits p ON d.produit_id = p.id 
    WHERE d.id = ?");
$query->execute([$id]);
$d = $query->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.6; }
        .header { text-align: right; border-bottom: 3px solid #444; padding-bottom: 20px; }
        .client-box { margin: 40px 0; padding: 20px; background: #f9f9f9; border-left: 5px solid #444; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th { background: #444; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .total-box { margin-top: 30px; text-align: right; font-size: 1.2em; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print"><button onclick="window.print()">Imprimer PDF</button> <a href="index.php">Retour Dashboard</a></div>
    
    <div class="header">
        <h1>PROPOSITION COMMERCIALE</h1>
        <p>Devis N° DEV-2026-<?= $d['id'] ?></p>
        <p>Date : <?= date('d/m/Y', strtotime($d['date_emission'])) ?></p>
    </div>

    <div class="client-box">
        <strong>À l'attention de :</strong><br>
        <?= htmlspecialchars($d['client_nom']) ?><br>
        <?= htmlspecialchars($d['client_email']) ?>
    </div>

    <table>
        <thead>
            <tr><th>Visuel</th><th>Description</th><th>Prix Unitaire</th><th>Quantité</th><th>Total HT</th></tr>
        </thead>
        <tbody>
            <tr>
                <td><img src='uploads/produits/<?= $d["image_path"] ?>' style='width:60px; height:auto; border-radius:4px;'></td>
                <td><?= htmlspecialchars($d['designation']) ?></td>
                <td><?= number_format($d['prix_unitaire'], 2) ?> €</td>
                <td><?= $d['quantite'] ?></td>
                <td><?= number_format($d['total_ht'], 2) ?> €</td>
            </tr>
        </tbody>
    </table>

    <div class="total-box">
        <p>Total HT : <?= number_format($d['total_ht'], 2) ?> €</p>
        <p>TVA (20%) : <?= number_format($d['total_ht'] * 0.2, 2) ?> €</p>
        <p><strong>TOTAL TTC : <?= number_format($d['total_ht'] * 1.2, 2) ?> €</strong></p>
    </div>

    <div style="margin-top: 100px; font-size: 0.8em; color: #777;">
        Bon pour accord (Signature et tampon précédés de la mention "Lu et approuvé") :
    </div>
</body>
</html>
