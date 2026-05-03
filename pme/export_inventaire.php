<?php
include 'includes/db.php';
$prods = $pdo->query("SELECT * FROM produits ORDER BY designation ASC")->fetchAll();
$valeur_totale = 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport d'Inventaire - <?= date('m/Y') ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { text-align: right; font-weight: bold; font-size: 14px; margin-top: 20px; }
        .signature { margin-top: 50px; display: flex; justify-content: space-between; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h1>ÉTAT DES STOCKS AU <?= date('d/m/Y') ?></h1>
        <p>Document certifié pour la comptabilité - PME ERP</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Référence</th>
                <th>Désignation</th>
                <th>Prix Unit. HT</th>
                <th>Quantité en Stock</th>
                <th>Valeur Stock HT</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($prods as $p): 
                $valeur = $p['stock_actuel'] * $p['prix_unitaire'];
                $valeur_totale += $valeur;
            ?>
            <tr>
                <td><?= $p['ref_interne'] ?></td>
                <td><?= $p['designation'] ?></td>
                <td><?= number_format($p['prix_unitaire'], 2) ?> €</td>
                <td><?= $p['stock_actuel'] ?></td>
                <td><?= number_format($valeur, 2) ?> €</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total">
        VALEUR TOTALE DE L'INVENTAIRE : <?= number_format($valeur_totale, 2) ?> €
    </div>

    <div class="signature">
        <p>Signature Responsable Entrepôt : ___________________</p>
        <p>Signature Direction : ___________________</p>
    </div>
</body>
</html>
